<?php

namespace Sparkfabrik\GitlabCiClient;

use Gitlab\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines a command that wraps Gitlab\Client.
 */
class GitlabActionsCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('gitlab:actions')
      ->setDescription('Gitlab Actions')
      ->addArgument('action', InputArgument::REQUIRED, 'Action to perform')
      ->addArgument('arguments', InputArgument::OPTIONAL, 'Arguments to pass to the action');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->validateEnvVariables($output);
    $private_token = getenv('GITLAB_PRIVATE_TOKEN');
    $gitlab_url = getenv('CI_SERVER_URL');

    $client = new Client();
    $client->setUrl($gitlab_url);
    $client->authenticate($private_token, Client::AUTH_HTTP_TOKEN);

    $method = $input->getArgument('action');
    $output->writeln("Given method: $method", OutputInterface::VERBOSITY_VERBOSE);
    $raw_arguments = $input->hasArgument('arguments') ? $input->getArgument('arguments') : "[]";
    $output->writeln("Given arguments: $raw_arguments", OutputInterface::VERBOSITY_VERBOSE);
    $arguments = json_decode($raw_arguments, TRUE);

    [$gitlab_resource_method, $action] = explode('.', $method);

    if (!method_exists($client, $gitlab_resource_method)) {
      $output->writeln("<error> $gitlab_resource_method  is not a valid method</error>");
      return 1;
    }

    $resource = $client->$gitlab_resource_method();

    if (!method_exists($resource, $action)) {
      $output->writeln("<error> $action is not a valid method on \"$gitlab_resource_method\" resource </error>");
      return 1;
    }

    $output->writeln(json_encode($arguments));
    $response = $resource->$action(...$arguments);
    $output->writeln(json_encode($response, JSON_PRETTY_PRINT));
    return 0;
  }

  /**
   * Validates that all required environment variables are set.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $output
   *   Output interface.
   */
  private function validateEnvVariables($output) {
    $required_variables = [
      'GITLAB_PRIVATE_TOKEN',
      'CI_SERVER_URL',
    ];
    foreach ($required_variables as $variable) {
      if (!getenv($variable)) {
        $output->writeln("<error>\"$variable\" env variable is not set.</error>");
        exit(1);
      }
    }
  }

}
