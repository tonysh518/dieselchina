<?php
class baseSfDefineDomainConfigHandler extends sfDefineEnvironmentConfigHandler
{
  /**
   * Executes this configuration handler.
   *
   * @param string $configFiles An absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   */
  public function execute($configFiles)
  {
    // get our prefix
    $prefix = strtolower($this->getParameterHolder()->get('prefix', ''));

    // add module prefix if needed
    if ($this->getParameterHolder()->get('module', false))
    {
      $wildcardValues = $this->getParameterHolder()->get('wildcardValues');
      // either the module name is in wildcard values, or it needs to be inserted on runtime
      $moduleName = $wildcardValues ? strtolower($wildcardValues[0]) : "'.strtolower(\$moduleName).'";
      $prefix .= $moduleName."_";
    }

    // parse the yaml
    $config = self::getConfiguration($configFiles);

    $values = array();
    foreach ($config as $category => $keys)
    {
      $values = array_merge($values, $this->getValues($prefix, $category, $keys));
    }

    $data = '';
    foreach ($values as $key => $value)
    {
      $data .= sprintf("  '%s' => %s,\n", $key, var_export($value, true));
    }

    // compile data
    $retval = '';
    if ($values)
    {
      $retval = "<?php\n".
                "// auto-generated by sfDefineEnvironmentConfigHandler\n".
                "// date: %s\nsfConfig::add(array(\n%s));\n";
      $retval = sprintf($retval, date('Y/m/d H:i:s'), $data);
    }

    return $retval;
  }


  /**
   * @see sfConfigHandler
   */
  static public function getConfiguration(array $configFiles)
  {
    return self::replaceConstants(self::flattenConfigurationWithEnvironment(self::parseYamls($configFiles)));
  }


  /**
   * Merges default, all and current environment configurations.
   *
   * @param array $config The main configuratino array
   *
   * @return array The merged configuration
   */
  static public function flattenConfigurationWithEnvironment($config)
  {
    $serverName = 'cli';
    if( isset( $_SERVER['SERVER_NAME'] ) ){
      $serverName = $_SERVER['SERVER_NAME'];
    }

    $parentDomain = baseSfDefineDomainConfigHandler::getParentDomain($serverName);

    return sfToolkit::arrayDeepMerge(
    isset($config['default']) && is_array($config['default']) ? $config['default'] : array(),
    isset($config['all']) && is_array($config['all']) ? $config['all'] : array(),
    isset($config[$serverName]) && is_array($config[$serverName]) ? $config[$serverName] : array(),
    isset($config[$parentDomain]) && is_array($config[$parentDomain]) ? $config[$parentDomain] : array()
    );
  }


  /**
   * Ritorna il parent domain di un server, ad esempio per collections.diesel.localhost => .diesel.localhost
   *
   * @param $domain il dominio completo
   * @return unknown_type
   */
  static public function getParentDomain($domain) {
    $domainArray = explode('.', $domain, 2);
    if( sizeof( $domainArray ) == 2 ){
      $domain = $domainArray[1];
    }
    return '.' . $domain;
  }

}