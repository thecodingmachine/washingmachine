<?php
namespace TheCodingMachine\WashingMachine\Gitlab;

use Gitlab\Client;
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

    public function sendCodeCoverageComment(CloverFile $cloverFile, CloverFile $previousCloverFile, string $projectName, int $mergeRequestId)
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


        // Note: there is a failure in the way Gitlab escapes HTML for the tables. Let's use this!.
        $message = sprintf('<table>
<tr>
<td>PHP&nbsp;code&nbsp;coverage:</td>
<td style="font-weight: bold">%.2f%%</td>
<td style="%s">%s</td>
<td width="99%%"></td>
</tr>
</table>', $cloverFile->getCoveragePercentage()*100, $style, $additionalText);

        $this->client->merge_requests->addComment($projectName, $mergeRequestId, $message);
    }

    public function sendDifferencesComments(CloverFile $cloverFile, CloverFile $previousCloverFile, string $projectName, string $commitId)
    {
        $differences = $this->diffService->getMeaningfulDifferences($cloverFile, $previousCloverFile);

        foreach ($differences as $difference) {
            if ($difference->isNew()) {

                $note = sprintf('<table>
<tr>
<td>Crap&score:</td>
<td style="font-weight: bold">%.2f%%</td>
<td width="99%%"></td>
</tr>
</table>', $difference->getCrapScore());
            } else {
                if ($difference->getCrapDifference() < 0) {
                    $style = 'background-color: #00994c; color: white';
                } else {
                    $style = 'background-color: #ff6666; color: white';
                }

                $note = sprintf('<table>
<tr>
<td>Crap&score: </td>
<td style="font-weight: bold">%.2f%%</td>
<td style="%s">(<em>%+.2f%%</em>)</td>
<td width="99%%"></td>
</tr>
</table>', $difference->getCrapScore(), $style, $difference->getCrapDifference());

            }

            $this->client->repositories->createCommitComment($projectName, $commitId, $note, [
                'path' => $difference->getFile(),
                'line' => $difference->getLine(),
                'line_type' => 'new'
            ]);
        }

    }
}
