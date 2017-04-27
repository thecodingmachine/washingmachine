<?php


namespace TheCodingMachine\WashingMachine\Git;


class GitRepository extends \Cz\Git\GitRepository
{
    public function getMergeBase(string $commit1, string $commit2) : string
    {
        $results = $this->extractFromCommand('git merge-base' . escapeshellarg($commit1). ' '. escapeshellarg($commit2));

        return $results[0];
    }
}