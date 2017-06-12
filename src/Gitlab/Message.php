<?php


namespace TheCodingMachine\WashingMachine\Gitlab;


use TheCodingMachine\WashingMachine\Clover\Analysis\Difference;
use TheCodingMachine\WashingMachine\Clover\CoverageDetectorInterface;
use TheCodingMachine\WashingMachine\Clover\CrapMethodFetcherInterface;
use TheCodingMachine\WashingMachine\Clover\DiffService;

/**
 * A class to build the message sent to Gitlab.
 */
class Message
{
    const MAX_NB_LINES_PER_FILE = 50;

    private $msg = '';

    public function addCoverageMessage(CoverageDetectorInterface $coverageDetector, CoverageDetectorInterface $previousCoverageDetector)
    {
        $coverage = $coverageDetector->getCoveragePercentage();
        $previousCoverage = $previousCoverageDetector->getCoveragePercentage();

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
        $this->msg .= sprintf('<table>
<tr>
<td>PHP&nbsp;code&nbsp;coverage:</td>
<td style="font-weight: bold">%.2f%%</td>
<td style="%s">%s</td>
<td width="99%%"></td>
</tr>
</table><br/>', $coverageDetector->getCoveragePercentage()*100, $style, $additionalText);
    }


    public function addDifferencesHtml(CrapMethodFetcherInterface $methodFetcher, CrapMethodFetcherInterface $previousMethodFetcher, DiffService $diffService, string $commitId, string $gitlabUrl, string $projectName)
    {
        $differences = $diffService->getMeaningfulDifferences($methodFetcher, $previousMethodFetcher);

        $this->msg .= $this->getDifferencesHtml($differences, $commitId, $gitlabUrl, $projectName);
    }

    public function addDifference(Difference $difference, string $commitId, string $gitlabUrl, string $projectName)
    {
        $this->msg .= $this->getDifferencesHtml([$difference], $commitId, $gitlabUrl, $projectName);
    }


    private function getDifferencesHtml(array $differences, string $commitId, string $gitlabUrl, string $projectName) : string
    {
        if (empty($differences)) {
            return "No meaningful differences in code complexity detected.\n";
        }

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
                $crapScoreEmoji = ' '.$crapScoreEmojiGenerator->getEmoji($difference->getCrapScore());
            }

            if ($difference->getFile() !== null) {
                $link = $this->getLinkToMethodInCommit($gitlabUrl, $projectName, $commitId, $difference->getFile(), $difference->getLine());
                $fullLink = sprintf('<a href="%s">%s</a>', $link, $difference->getMethodShortName());
            } else {
                $fullLink = $difference->getMethodShortName();
            }

            $tableRows .= sprintf('<tr>
<td><code>%s</code></td>
<td style="text-align:center">%d%s</td>
<td style="text-align:center;%s">%s</td>
</tr>', $fullLink, $difference->getCrapScore(), $crapScoreEmoji, $style, $differenceCol);
        }

        return sprintf($tableTemplate, $tableRows);
    }

    private function getLinkToMethodInCommit(string $gitlabUrl, string $projectName, string $commit, string $filePath, int $line)
    {
        return rtrim($gitlabUrl, '/').'/'.$projectName.'/blob/'.$commit.'/'.ltrim($filePath, '/').'#L'.$line;
    }

    public function addFile(\SplFileInfo $file, string $gitlabUrl, string $projectName, string $buildId)
    {
        list($text, $isComplete) = $this->getFirstLines($file, self::MAX_NB_LINES_PER_FILE);

        $text = str_replace('```', '\\```', $text);
        $text = str_replace('~~~', '\\~~~', $text);

        $url = $this->getArtifactFileUrl($file->getFilename(), $gitlabUrl, $projectName, $buildId);

        $this->msg .= sprintf("\n<strong>[%s](%s)</strong>\n", $file->getFilename(), $url);
        $this->msg .= sprintf("```\n%s%s```\n", $text, $isComplete?'':"... (file truncated)\n");

        if (!$isComplete) {
            $this->msg .= sprintf("[Download complete file](%s)\n", $url);
        }
    }

    private function getArtifactFileUrl(string $fileName, string $gitlabUrl, string $projectName, int $buildId) : string
    {
        return $gitlabUrl.'/'.$projectName.'/builds/'.$buildId.'/artifacts/file/'.$fileName;
    }

    /**
     * Returns the first lines of a file
     *
     * @param \SplFileInfo $file
     * @param int $maxLines
     * @return array First element: the string, second element: whether the returned string represents the whole file or not.
     */
    private function getFirstLines(\SplFileInfo $file, int $maxLines) : array
    {
        // Let's get 50 lines at most.
        $cnt = $maxLines;
        $fileObject = $file->openFile();
        $str = '';
        while (!$fileObject->eof() && $cnt !== 0) {
            $str .= $fileObject->fgets();
            $cnt--;
        }

        return [$str, $cnt !== 0];
    }

    public function __toString()
    {
        return $this->msg;
    }

}