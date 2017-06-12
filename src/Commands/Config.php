<?php


namespace TheCodingMachine\WashingMachine\Commands;


use Cz\Git\GitRepository;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Object to retrieve the command configuration based on environment variables and input.
 */
class Config
{
    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public function getCloverFilePath() : string
    {
        return $this->input->getOption('clover');
    }

    public function getCrap4JFilePath() : string
    {
        return $this->input->getOption('crap4j');
    }

    public function getGitlabApiToken() : string
    {
        $gitlabApiToken = $this->input->getOption('gitlab-api-token');
        if ($gitlabApiToken === null) {
            $gitlabApiToken = getenv('GITLAB_API_TOKEN');
            if ($gitlabApiToken === false) {
                throw new \RuntimeException('Could not find the Gitlab API token in the "GITLAB_API_TOKEN" environment variable. Either set this environment variable or pass the token via the --gitlab-api-token command line option.');
            }
        }
        return $gitlabApiToken;
    }

    public function getGitlabUrl() : string
    {
        $gitlabUrl = $this->input->getOption('gitlab-url');
        if ($gitlabUrl === null) {
            $ciProjectUrl = getenv('CI_REPOSITORY_URL');
            if ($ciProjectUrl === false) {
                $ciProjectUrl = getenv('CI_BUILD_REPO');
                if ($ciProjectUrl === false) {
                    throw new \RuntimeException('Could not find the Gitlab URL in the "CI_REPOSITORY_URL" (Gitlab 9+) or "CI_BUILD_REPO" (Gitlab 8.x) environment variable (usually set by Gitlab CI). Either set this environment variable or pass the URL via the --gitlab-url command line option.');
                }
            }
            $parsed_url = parse_url($ciProjectUrl);
            $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
            $gitlabUrl = $scheme.$host.$port;
        }
        return rtrim($gitlabUrl, '/');
    }

    public function getGitlabApiUrl() : string
    {
        return $this->getGitlabUrl().'/api/v3/';
    }

    public function getGitlabProjectName() : string
    {
        $projectName = $this->input->getOption('gitlab-project-name');
        if ($projectName === null) {
            $projectDir = getenv('CI_PROJECT_DIR');
            if ($projectDir === false) {
                throw new \RuntimeException('Could not find the Gitlab project name in the "CI_PROJECT_DIR" environment variable (usually set by Gitlab CI). Either set this environment variable or pass the project name via the --gitlab-project-name command line option.');
            }
            $projectName = substr($projectDir, 8);
        }
        return $projectName;
    }

    public function getCommitSha() : string
    {
        $commitSha = $this->input->getOption('commit-sha');

        if ($commitSha === null) {
            $commitSha = getenv('CI_COMMIT_SHA');
            if ($commitSha === false) {
                $commitSha = getenv('CI_BUILD_REF');
                if ($commitSha === false) {
                    throw new \RuntimeException('Could not find the Gitlab build reference in the "CI_COMMIT_SHA" (Gitlab 9+) or "CI_BUILD_REF" (Gitlab 8.x) environment variable (usually set by Gitlab CI). Either set this environment variable or pass the build reference via the --commit-sha command line option.');
                }
            }
        }

        return $commitSha;
    }

    public function getGitlabBuildId() : int
    {
        $buildId = $this->input->getOption('gitlab-job-id');
        if ($buildId === null) {
            $buildId = getenv('CI_BUILD_ID');
            if ($buildId === false) {
                $buildId = getenv('CI_JOB_ID');
                if ($buildId === false) {
                    throw new \RuntimeException('Could not find the Gitlab build id in the "CI_JOB_ID" (Gitlab 9+) or "CI_BUILD_ID" (Gitlab 8.x) environment variable (usually set by Gitlab CI). Either set this environment variable or pass the build id via the --gitlab-job-id command line option.');
                }
            }
        }
        return $buildId;
    }

    /**
     * Returns the current branch name (from Git)
     * @return string
     */
    public function getCurrentBranchName() : string
    {
        // Gitlab 8.x
        $branchName = getenv('CI_BUILD_REF_NAME');
        if ($branchName !== false) {
            return $branchName;
        }

        // Gitlab 9+
        $branchName = getenv('CI_COMMIT_REF_NAME');
        if ($branchName !== false) {
            return $branchName;
        }

        $repo = new GitRepository(getcwd());
        return $repo->getCurrentBranchName();
    }

    public function getFiles() : array
    {
        return $this->input->getOption('file');
    }

    public function isOpenIssue() : bool
    {
        return $this->input->getOption('open-issue');
    }
}
