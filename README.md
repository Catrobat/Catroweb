WORK IN PROGRESS !!!
====================

If you want to checkout Catroweb, have a look at this repository: https://github.com/Catrobat/Catroweb


HowTo for installing Sonata
---------------------------

  - ```$ php composer.phar install```
  - ```$ php app/console doctrine:schema:update --force```
  - ```$ php app/console init:acl```
  - ```$ php app/console fos:user:create --super-admin```
  - ```$ php app/console sonata:admin:setup-acl```
  - ```$ php app/console sonata:admin:generate-object-acl```
  - copy the line  
      ```'security.acl.permission.map.class: Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap'```  
      from  
      ```parameters.yml.dist```  
      into  
      ```parameters.yml```
  - now go to ```catroid.local/app_dev.php/admin``` and enjoy ;)

Tests
-----

Make sure every test works before pushing to master!

  - ```$ php bin/behat -p=api```
  - ```$ php bin/phpspec run```

