<?php
namespace TheCodingMachine\WashingMachine\Gitlab;

use Gitlab\Client;
use TheCodingMachine\WashingMachine\Clover\Analysis\Difference;
use TheCodingMachine\WashingMachine\Clover\CrapMethodFetcherInterface;
use TheCodingMachine\WashingMachine\Clover\DiffService;

class SendCommentService
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var DiffService
     */
    private $diffService;

    public function __construct(Client $client, DiffService $diffService)
    {
        $this->client = $client;
        $this->diffService = $diffService;
    }


    public function sendDifferencesCommentsInCommit(CrapMethodFetcherInterface $cloverFile, CrapMethodFetcherInterface $previousCloverFile, string $projectName, string $commitId, string $gitlabUrl)
    {
        $differences = $this->diffService->getMeaningfulDifferences($cloverFile, $previousCloverFile);

        foreach ($differences as $difference) {
            $message = new Message();
            $message->addDifference($difference, $commitId, $gitlabUrl, $projectName);

            $options = [];
            if ($difference->getFile() !== null) {
                $options = [
                    'path' => $difference->getFile(),
                    'line' => $difference->getLine(),
                    'line_type' => 'new'
                ];
            }

            $this->client->repositories->createCommitComment($projectName, $commitId, (string) $message, $options);
        }
    }
}
