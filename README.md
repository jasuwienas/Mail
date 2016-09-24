# Symfony2 Mail
Integrates mail into Symfony2 project.

## Installation

1. Add as composer dependency:

  ```bash
  composer require jasuwienas/mail
  ```
2. Add in application kernel:

  ```php
  class AppKernel extends Kernel
  {
      public function registerBundles()
      {
      //...
      $bundles[] = new \Jasuwienas\MailBundle\MailBundle();
      return $bundles;
      }
  }
  ```
3. Mail sending command:
php app/console mail:send