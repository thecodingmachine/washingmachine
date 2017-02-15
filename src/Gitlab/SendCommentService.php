<?php
namespace TheCodingMachine\WashingMachine\Gitlab;

use Gitlab\Client;
use TheCodingMachine\WashingMachine\Clover\Analysis\Difference;
use TheCodingMachine\WashingMachine\Clover\CloverFileInterface;
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

    public function sendCodeCoverageCommentToMergeRequest(CloverFileInterface $cloverFile, CloverFileInterface $previousCloverFile, string $projectName, int $mergeRequestId, string $commitId, string $gitlabUrl)
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
        if (count($differences) > 0) {
            $differencesHtml = $this->getDifferencesHtml($differences, $commitId, $gitlabUrl, $projectName);
        } else {
            $differencesHtml = 'No major differences in C.R.A.P. score found.';
        }

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

    public function sendDifferencesCommentsInCommit(CloverFileInterface $cloverFile, CloverFileInterface $previousCloverFile, string $projectName, string $commitId, string $gitlabUrl)
    {
        $differences = $this->diffService->getMeaningfulDifferences($cloverFile, $previousCloverFile);

        foreach ($differences as $difference) {
            $note = $this->getDifferencesHtml([ $difference ], $commitId, $gitlabUrl, $projectName);

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
    private function getDifferencesHtml(array $differences, string $commitId, string $gitlabUrl, string $projectName) : string
    {
        $tableTemplate = '<table>
<tr>
<th></th>
<th>C.R.A.P.</th>
<th>Variation</th>
<th width="99%%"></th>
</tr>
%s
</table>';
        $tableRows = '';
        $crapScoreEmojiGenerator = EmojiGenerator::createCrapScoreEmojiGenerator();
        foreach ($differences as $difference) {
            $style = '';
            if (!$difference->isNew()) {

                if ($difference->getCrapDifference() < 0) {
                    $style = 'background-color: #00994c; color: white';
                } else {
                    $style = 'background-color: #ff6666; color: white';
                }
                $differenceCol = sprintf('%+d', $difference->getCrapDifference());
                $crapScoreEmoji = '';
            } else {
                $differenceCol = '<em>New</em>';
                // For new rows, let's display an emoji
                $crapScoreEmoji = $crapScoreEmojiGenerator->getEmoji($difference->getCrapScore());
            }

            $link = $this->getLinkToMethodInCommit($gitlabUrl, $projectName, $commitId, $difference->getFile(), $difference->getLine());

            $tableRows .= sprintf('<tr>
<td><code><a href="%s">%s</a></code></td>
<td style="text-align:center">%d%s</td>
<td style="text-align:center;%s">%s</td>
</tr>', $link, $difference->getMethodShortName(), $difference->getCrapScore(), $crapScoreEmoji, $style, $differenceCol);
        }

        return sprintf($tableTemplate, $tableRows);
    }

    private function getLinkToMethodInCommit(string $gitlabUrl, string $projectName, string $commit, string $filePath, int $line)
    {
        return rtrim($gitlabUrl, '/').'/'.$projectName.'/blob/'.$commit.'/'.ltrim($filePath, '/').'#L'.$line;
    }
}
