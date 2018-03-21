<?php
namespace TheCodingMachine\WashingMachine\Gitlab;
use Gitlab\Client;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Log\LoggerInterface;
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
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
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
            // Let's only return this PR if the returned commit is the FIRST one (otherwise, the commit ID is on an outdated version of the PR)

            if ($mergeRequest['sha'] === $commitSha) {
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

    private $pipelines = [];

    private function getPipelines(string $projectName) : array
    {
        if (!isset($this->pipelines[$projectName])) {
            $this->pipelines[$projectName] = $this->client->projects->pipelines($projectName);
        }
        return $this->pipelines[$projectName];
    }

    public function findPipelineByCommit(string $projectName, string $commitId) : ?array
    {
        $pipelines = $this->getPipelines($projectName);

        foreach ($pipelines as $pipeline) {
            if ($pipeline['sha'] === $commitId) {
                return $pipeline;
            }
        }

        return null;
    }

    /**
     * Recursive function that attempts to find a build in the previous commits.
     *
     * @param string $projectName
     * @param string $commitId
     * @param string|null $excludePipelineId A pipeline ID we want to exclude (we don't want to get the current pipeline ID).
     * @param int $numIter
     * @return array
     * @throws BuildNotFoundException
     */
    public function getLatestPipelineFromCommitId(string $projectName, string $commitId, string $excludePipelineId = null, int $numIter = 0) : array
    {
        $this->logger->debug('Looking for pipeline for commit '.$commitId);
        $pipeline = $this->findPipelineByCommit($projectName, $commitId);

        if ($pipeline !== null && $pipeline['id'] !== $excludePipelineId) {
            $this->logger->debug('Found pipeline '.$pipeline['id'].' for commit '.$commitId);
            return $pipeline;
        }
        if ($pipeline['id'] === $excludePipelineId) {
            $this->logger->debug('Ignoring pipeline '.$excludePipelineId.' for commit '.$commitId);
        }

        $numIter++;
        // Let's find a build in the last 10 commits.
        if ($numIter > 10) {
            $this->logger->debug('Could not find a build for commit '.$projectName.':'.$commitId.', after iterating on 10 parent commits.');
            throw new BuildNotFoundException('Could not find a build for commit '.$projectName.':'.$commitId);
        }
        $this->logger->debug('Could not find a build for commit '.$projectName.':'.$commitId.'. Looking for a build in parent commit.');

        // Let's get the commit info
        $commit = $this->client->repositories->commit($projectName, $commitId);
        $parentIds = $commit['parent_ids'];

        if (count($parentIds) !== 1) {
            $this->logger->debug('Cannot look into parent commit because it is a merge from 2 branches.');
            throw new BuildNotFoundException('Could not find a build for commit '.$projectName.':'.$commitId);
        }

        // Not found? Let's recurse.
        return $this->getLatestPipelineFromCommitId($projectName, $parentIds[0], $excludePipelineId, $numIter);
    }

    /**
     * @param string $projectName
     * @param string $branchName
     * @param string $excludePipelineId A pipeline ID we want to exclude (we don't want to get the current pipeline ID).
     * @return array
     * @throws BuildNotFoundException
     */
    public function getLatestPipelineFromBranch(string $projectName, string $branchName, string $excludePipelineId) : array
    {
        $commitId = $this->getLatestCommitIdFromBranch($projectName, $branchName);

        try {
            return $this->getLatestPipelineFromCommitId($projectName, $commitId, $excludePipelineId);
        } catch (BuildNotFoundException $e) {
            throw new BuildNotFoundException('Could not find a build for branch '.$projectName.':'.$branchName, 0, $e);
        }
    }

    /**
     * @param string $projectName
     * @param string $pipelineId
     * @param string $buildName
     * @param string $jobStage
     * @param string $file
     * @throws BuildNotFoundException
     */
    public function dumpArtifact(string $projectName, string $pipelineId, string $buildName, string $jobStage, string $file)
    {
        // Call seems broken
        //$artifactContent = $this->client->jobs->artifactsByRefName($projectName, $buildRef, $jobName);

        $jobs = $this->client->jobs->pipelineJobs($projectName, $pipelineId);
        $job = null;
        foreach ($jobs as $jobItem) {
            if ($jobItem['name'] === $buildName && $jobItem['stage'] === $jobStage && (in_array($jobItem['status'], ['failed', 'success']))) {
                $job = $jobItem;
                break;
            }
        }

        if ($job === null) {
            throw new BuildNotFoundException('Could not find finished job with build name "'.$buildName.'" and stage "'.$jobStage.'" in pipeline "'.$pipelineId.'"');
        }

        $artifactContent = $this->client->jobs->artifacts($projectName, $job['id']);

        $stream = StreamWrapper::getResource($artifactContent);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, $stream);
    }

    /**
     * @param string $projectName
     * @param string $branchName
     * @param string $buildName
     * @param string $jobStage
     * @param string $file
     * @param string $excludePipelineId A pipeline ID we want to exclude (we don't want to get the current pipeline ID).
     * @throws BuildNotFoundException
     */
    public function dumpArtifactFromBranch(string $projectName, string $branchName, string $buildName, string $jobStage, string $file, string $excludePipelineId)
    {
        $pipeline = $this->getLatestPipelineFromBranch($projectName, $branchName, $excludePipelineId);
        $this->dumpArtifact($projectName, $pipeline['id'], $buildName, $jobStage, $file);
    }
}
