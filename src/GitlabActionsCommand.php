<?php

namespace Sparkfabrik\GitlabCiClient;

use Gitlab\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitlabActionsCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('gitlab:actions')
      ->setDescription('Gitlab Actions')
      ->addArgument('action', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Action to perform');
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

    $arguments = $input->getArgument('action');
    $method = array_shift($arguments);
    $arguments = $this->resolveArguments($arguments);

    [$gitlab_resource_method, $action] = explode('.', $method);

    if (!method_exists($client, $gitlab_resource_method)) {
      $output->writeln("<error> $gitlab_resource_method  is not a valid method</error>");
      exit(1);
    }

    $resource = $client->$gitlab_resource_method();

    if (!method_exists($resource, $action)) {
      $output->writeln("<error> $action is not a valid method on \"$gitlab_resource_method\" resource </error>");
      exit(1);
    }

    $output->writeln(json_encode($arguments));
    $response = $resource->$action(...$arguments);
    $output->writeln(json_encode($response, JSON_PRETTY_PRINT));
    exit(0);
  }

  /**
   * Resolve arguments for the client.
   *
   * Arguments are separated by space or newline and are treated as strings.
   * To handle complex arguments like associative array or array of arrays, it
   * is possible to pass a json like string as single argument.
   *
   * An example: "{\"iids\":[185,184];state:all}" as single argument will
   * become an associative array like this:
   * [
   *   'iids' => [185,184],
   *   'state' => 'all'
   * ]
   *
   * @param array $arguments
   *   An array of arguments to parse.
   *
   * @return array
   *   Resolved arguments as array.
   */
  private function resolveArguments(array $arguments)
  : array {
    return array_map(fn ($argument) => $this->resolveArgument($argument), $arguments);
  }


  /**
   * Resolve a single string argument.
   *
   * @param mixed $argument
   *   Argument to parse.
   *
   * @return string|array|int|null
   *   Parsed value.
   *
   * @see resolveArguments().
   */
  private function resolveArgument(mixed $argument)
  : string|array|int|null {
    if (str_starts_with($argument, '{') || str_starts_with($argument, '[')) {
      $arguments = [];
      $argument_data = explode(';',substr($argument, 1, -1));
      foreach ($argument_data as $argument_datum) {
        if (str_contains($argument_datum, ':')) {
          [$key, $value] = explode(':', $argument_datum);
          $arguments[$key] =  $this->resolveArgument($value);
        }
        else {
          // It might be a list of values separated by commas.
          $argument_datum = explode(',', $argument_datum);
          return array_map(function($arg) {
            return $this->castType($arg);
          }, $argument_datum);
        }
      }
      return $arguments;
    }
    else {
      return $this->castType($argument);
    }
  }

  /**
   * Convert a string to integer if possible.
   *
   * @param mixed $argument
   *   Argument to parse.
   *
   * @return int|string|null
   *   Parsed value.
   */
  private function castType(mixed $argument)
  : int|string|null {
    if (is_numeric($argument)) {
      return (int) $argument;
    }
    if (is_bool($argument)) {
      return strtolower($argument) === 'true';
    }

    if ("null" === $argument) {
      return NULL;
    }

    return $argument;
  }

  /**
   * Validates that all required environment variables are set.
   *
   * @param InputInterface $output
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
