<?php
namespace TheCodingMachine\WashingMachine\Gitlab;

use Gitlab\Client;
use TheCodingMachine\WashingMachine\Clover\Analysis\Difference;
use TheCodingMachine\WashingMachine\Clover\CloverFile;
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

    public function sendCodeCoverageCommentToMergeRequest(CloverFile $cloverFile, CloverFile $previousCloverFile, string $projectName, int $mergeRequestId)
    {
        $coverage = $cloverFile->getCoveragePercentage();
        $previousCoverage = $previousCloverFile->getCoveragePercentage();

        $additionalText = '';
        $style = '';
        if ($coverage > $previousCoverage + 0.0001) {
            $additionalText = sprintf('(<em>+%.2f%%</em>)', ($coverage - $previousCoverage)*100);
            $style .= 'background-color: #00994c; color: white';
        } elseif ($coverage < $previousCoverage - 0.0001) {
            $additionalText = sprintf('(<em>-%.2f%%</em>)', ($previousCoverage - $coverage)*100);
            $style .= 'background-color: #ff6666; color: white';
        }

        $differences = $this->diffService->getMeaningfulDifferences($cloverFile, $previousCloverFile);
        $differencesHtml = $this->getDifferencesHtml($differences);

        // Note: there is a failure in the way Gitlab escapes HTML for the tables. Let's use this!.
        $message = sprintf('<table>
<tr>
<td>PHP&nbsp;code&nbsp;coverage:</td>
<td style="font-weight: bold">%.2f%%</td>
<td style="%s">%s</td>
<td width="99%%"></td>
</tr>
</table><br/>%s', $cloverFile->getCoveragePercentage()*100, $style, $additionalText, $differencesHtml);

        $this->client->merge_requests->addComment($projectName, $mergeRequestId, $message);
    }

    public function sendDifferencesCommentsInCommit(CloverFile $cloverFile, CloverFile $previousCloverFile, string $projectName, string $commitId)
    {
        $differences = $this->diffService->getMeaningfulDifferences($cloverFile, $previousCloverFile);

        foreach ($differences as $difference) {
            $note = $this->getDifferencesHtml([ $difference ]);

            $this->client->repositories->createCommitComment($projectName, $commitId, $note, [
                'path' => $difference->getFile(),
                'line' => $difference->getLine(),
                'line_type' => 'new'
            ]);
        }

    }

    /**
     * @param Difference[] $differences
     * @return string
     */
    private function getDifferencesHtml(array $differences) : string
    {
        $tableTemplate = '<table>
<tr>
<th></th>
<th>Crap&nbsp;score</th>
<th>Variation</th>
<th width="99%%"></th>
</tr>
%s
</table>';
        $tableRows = '';
        foreach ($differences as $difference) {
            $style = '';
            if (!$difference->isNew()) {

                if ($difference->getCrapDifference() < 0) {
                    $style = 'background-color: #00994c; color: white';
                } else {
                    $style = 'background-color: #ff6666; color: white';
                }
                $differenceCol = sprintf('%+f', $difference->getCrapDifference());
            } else {
                $differenceCol = '<em>New</em>';
                // TODO: for new rows, it would be really cool to display a color code for the global CRAP score.
            }

            $tableRows .= sprintf('<tr>
<td>%s</td>
<td>%f</td>
<td %s>%s</td>
</tr>', $difference->getMethodShortName(), $difference->getCrapScore(), $style, $differenceCol);
        }

        return sprintf($tableTemplate, $tableRows);
    }
}
