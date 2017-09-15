<?php


namespace TheCodingMachine\WashingMachine\Git;


use Cz\Git\GitException;

class GitRepository extends \Cz\Git\GitRepository
{
    public function getMergeBase(string $commit1, string $commit2) : string
    {
        try {
            $this->extractFromCommand('git merge-base --is-ancestor ' . escapeshellarg($commit1) . ' ' . escapeshellarg($commit2));
        } catch (GitException $e) {
            // The command will return exit code 1 if $commit1 is an ancestor of $commit2
            // Exit code one triggers an exception. We catch it.
            return $commit1;
        }


        $results = $this->extractFromCommand('git merge-base ' . escapeshellarg($commit1). ' '. escapeshellarg($commit2));

        return $results[0];
    }

    public function getLatestCommitForBranch(string $branch) : string
    {
        $results = $this->extractFromCommand('git log -n 1 --pretty=format:"%H" ' . escapeshellarg($branch));

        return $results[0];
    }
}