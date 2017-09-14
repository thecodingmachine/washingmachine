<?php
namespace TheCodingMachine\WashingMachine\Gitlab;
use Gitlab\Client;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class to access different data in Gitlab from the build reference
 */
class BuildService
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {

        $this->client = $client;
    }

    /**
     * Returns a commit ID from a project name and build ref.
     *
     * @param string $projectName
     * @param string $buildRef
     * @return string
     */
    public function getCommitId(string $projectName, string $buildRef) : string
    {
        $build = $this->client->projects->build($projectName, $buildRef);
        return $build['commit']['id'];
    }

    /**
     * @param string $projectName
     * @param string $commitSha
     * @return array The merge request object
     * @throws MergeRequestNotFoundException
     */
    public function findMergeRequestByCommitSha(string $projectName, string $commitSha) : array
    {
        // Find in the merge requests (since our build was triggered recently, it should definitely be there)
        $mergeRequests = $this->client->merge_requests->all($projectName, [
            'order_by' => 'updated_at',
            'sort' => 'desc'
        ]);

        foreach ($mergeRequests as $mergeRequest) {
            $commits = $this->client->merge_requests->commits($projectName, $mergeRequest['id']);
            // Let's only return this PR if the returned commit is the FIRST one (otherwise, the commit ID is on an outdated version of the PR)

            // Note: strangely, the "id" column of the commit is the build ref and not the commit id... weird!
            if ($commits[0]['id'] === $commitSha) {
                return $mergeRequest;
            }
        }

        throw new MergeRequestNotFoundException('Could not find a PR whose last commit/buildRef ID is '.$commitSha);
    }

    public function getLatestCommitIdFromBranch(string $projectName, string $branchName) : string
    {
        $branch = $this->client->repositories->branch($projectName, $branchName);
        return $branch['commit']['id'];
    }

    /**
     * Recursive function that attempts to find a build in the previous commits.
     *
     * @param string $projectName
     * @param string $commitId
     * @param int $numIter
     * @return array
     * @throws BuildNotFoundException
     */
    public function getLatestBuildFromCommitId(string $projectName, string $commitId, int $numIter = 0) : array
    {
        $builds = $this->client->repositories->commitBuilds($projectName, $commitId, ['failed', 'success']);

        if (!empty($builds)) {
            // TODO: check that builds are ordered in reverse date order!!!
            return $builds[0];
        }

        $numIter++;
        // Let's find a build in the last 10 commits.
        if ($numIter > 10) {
            throw new BuildNotFoundException('Could not find a build for commit '.$projectName.':'.$commitId);
        }

        // Let's get the commit info
        $commit = $this->client->repositories->commit($projectName, $commitId);
        $parentIds = $commit['parent_ids'];

        if (count($parentIds) !== 1) {
            throw new BuildNotFoundException('Could not find a build for commit '.$projectName.':'.$commitId);
        }

        // Not found? Let's recurse.
        return $this->getLatestBuildFromCommitId($projectName, $parentIds[0], $numIter);
    }

    /**
     * @param string $projectName
     * @param string $branchName
     * @return array
     * @throws BuildNotFoundException
     */
    public function getLatestBuildFromBranch(string $projectName, string $branchName) : array
    {
        $commitId = $this->getLatestCommitIdFromBranch($projectName, $branchName);

        try {
            return $this->getLatestBuildFromCommitId($projectName, $commitId);
        } catch (BuildNotFoundException $e) {
            throw new BuildNotFoundException('Could not find a build for branch '.$projectName.':'.$branchName, 0, $e);
        }
    }

    public function dumpArtifact(string $projectName, string $buildRef, string $jobName, string $file)
    {
        $artifactContent = $this->client->jobs->artifactsByRefName($projectName, $buildRef, $jobName);

        $stream = StreamWrapper::getResource($artifactContent);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, $stream);
    }

    public function dumpArtifactFromBranch(string $projectName, string $branchName, string $jobStage, string $file)
    {
        $build = $this->getLatestBuildFromBranch($projectName, $branchName);
        $this->dumpArtifact($projectName, $build['id'], $jobStage, $file);
    }
}
