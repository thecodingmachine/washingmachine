<?php
namespace TheCodingMachine\WashingMachine\Gitlab;
use Gitlab\Client;
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
     * @param string $buildRef
     * @return array The merge request object
     * @throws MergeRequestNotFoundException
     */
    public function findMergeRequestByBuildRef(string $projectName, string $buildRef) : array
    {
        // Find in the last 50 merge requests (since our build was triggered recently, it should definitely be there)
        $mergeRequests = $this->client->merge_requests->all($projectName, 1, 50, 'updated_at', 'desc');

        foreach ($mergeRequests as $mergeRequest) {
            $commits = $this->client->merge_requests->commits($projectName, $mergeRequest['id']);
            // Let's only return this PR if the returned commit is the FIRST one (otherwise, the commit ID is on an outdated version of the PR)

            // Note: strangely, the "id" column of the commit is the build ref and not the commit id... weird!
            if ($commits[0]['id'] === $buildRef) {
                return $mergeRequest;
            }
        }

        throw new MergeRequestNotFoundException('Could not find a PR (in the 50 last PRs) whose last commit/buildRef ID is '.$buildRef);
    }

    public function getLatestCommitIdFromBranch(string $projectName, string $branchName) : string
    {
        $branch = $this->client->repositories->branch($projectName, $branchName);
        return $branch['commit']['id'];
    }

    public function getLatestBuildFromBranch(string $projectName, string $branchName) : array
    {
        $commitId = $this->getLatestCommitIdFromBranch($projectName, $branchName);
        $builds = $this->client->repositories->commitBuilds($projectName, $commitId);

        if (!empty($builds)) {
            // TODO: check that builds are ordered in reverse date order!!!
            return $builds[0];
        }

        throw new \RuntimeException('Could not find a build for branch '.$projectName.':'.$branchName);
    }

    public function dumpArtifact(string $projectName, string $buildRef, string $file)
    {
        $artifactContent = $this->client->projects->buildArtifacts($projectName, $buildRef);
        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, $artifactContent);
    }

    public function dumpArtifactFromBranch(string $projectName, string $branchName, string $file)
    {
        $build = $this->getLatestBuildFromBranch($projectName, $branchName);
        $this->dumpArtifact($projectName, $build['id'], $file);
    }
}
