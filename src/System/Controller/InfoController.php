<?php

namespace App\System\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InfoController extends AbstractController
{
  #[Route(path: 'info/php', methods: ['GET'])]
  public function phpInfo(): void
  {
    phpinfo();
    exit;
  }

  /**
   * @throws Exception
   */
  #[Route(path: '/info/db', methods: ['GET'])]
  public function databaseInfo(Connection $connection): Response
  {
    $sql = 'SHOW GLOBAL VARIABLES';
    $stmt = $connection->prepare($sql);
    $result = $stmt->executeQuery();
    echo '<table style="width: 100%;">';
    echo '<tr style="background-color: #f2f2f2;"><th>Database Variable</th><th>Value</th></tr>';
    $i = 0;
    while ($variable = $result->fetchAssociative()) {
      $color = (0 == $i % 2) ? '#f2f2f2' : '#ffffff';
      echo '<tr style="background-color: '.$color.';"><td>'.$variable['Variable_name'].'</td><td>'.$variable['Value'].'</td></tr>';
      ++$i;
    }
    echo '</table>';
    exit;
  }
}
