<?php

namespace Drupal\FunctionalTests\Installer;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the installer with an existing settings file but no install profile.
 *
 * @group Installer
 */
class InstallerExistingSettingsNoProfileTest extends InstallerTestBase {

  /**
   * {@inheritdoc}
   *
   * Configures a preexisting settings.php file without an install_profile
   * setting before invoking the interactive installer.
   */
  protected function prepareEnvironment() {
    parent::prepareEnvironment();

    // Pre-configure hash salt.
    // Any string is valid, so simply use the class name of this test.
    $this->settings['settings']['hash_salt'] = (object) [
      'value' => __CLASS__,
      'required' => TRUE,
    ];

    // Pre-configure database credentials.
    $connection_info = Database::getConnectionInfo();
    unset($connection_info['default']['pdo']);
    unset($connection_info['default']['init_commands']);

    $this->settings['databases']['default'] = (object) [
      'value' => $connection_info,
      'required' => TRUE,
    ];

    // Pre-configure config directories.
    $this->settings['config_directories'] = [
      CONFIG_SYNC_DIRECTORY => (object) [
        'value' => DrupalKernel::findSitePath(Request::createFromGlobals()) . '/files/config_sync',
        'required' => TRUE,
      ],
    ];
    mkdir($this->settings['config_directories'][CONFIG_SYNC_DIRECTORY]->value, 0777, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpSettings() {
    // This step should not appear, since settings.php is fully configured
    // already.
  }

  /**
   * Verifies that installation succeeded.
   */
  public function testInstaller() {
    $this->assertUrl('user/1');
    $this->assertResponse(200);
    $this->assertEqual('testing', \Drupal::installProfile());
  }

}
