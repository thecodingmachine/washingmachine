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
            $ciProjectUrl = getenv('CI_BUILD_REPO');
            if ($ciProjectUrl === false) {
                throw new \RuntimeException('Could not find the Gitlab URL in the "CI_BUILD_REPO" environment variable (usually set by Gitlab CI). Either set this environment variable or pass the URL via the --gitlab-url command line option.');
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

    public function getGitlabBuildRef() : string
    {
        $buildRef = $this->input->getOption('gitlab-build-ref');
        if ($buildRef === null) {
            $buildRef = getenv('CI_BUILD_REF');
            if ($buildRef === false) {
                throw new \RuntimeException('Could not find the Gitlab build reference in the "CI_BUILD_REF" environment variable (usually set by Gitlab CI). Either set this environment variable or pass the build reference via the --gitlab-build-ref command line option.');
            }
        }
        return $buildRef;
    }

    /**
     * Returns the current branch name (from Git)
     * @return string
     */
    public function getCurrentBranchName() : string
    {
        $repo = new GitRepository(getcwd());
        return $repo->getCurrentBranchName();
    }
}
