<?php

// Support the methods to collect hardware informations
require_once __DIR__ . '/ComponentsNotification.php';
// Support the functions to send an email
require_once __DIR__ . '/send.php';

	$mastermind = new ComponentsNotification();
	
	$mastermind->get_cpus();	
	$mastermind->get_memories();
	$mastermind->get_monitors();
	$mastermind->get_storages();	
	$mastermind->get_videos();

  $added_hardware = $mastermind->added_json();
  $removed_hardware = $mastermind->removed_json();
	
	// html_part_addition e html_part_remove are public attributes of ComponentesNotification
	$body_mail = $mastermind->html_part_addition . $mastermind->html_part_remove;

  $added_hardware = $mastermind->added_json();
  $removed_hardware = $mastermind->removed_json();

	if ($body_mail != '') {
    try {
      if(!empty($added_hardware)) {
        exec('rm -rf notification_add.txt');

        $filename = 'notification_add.txt';
        exec('touch ' . $filename);
        file_put_contents($filename, $added_hardware);

        $command = 'python ./webhook.py ' . 'add';

        exec($command);  
      }

      if (!empty($removed_hardware)) {
        exec('rm -rf notification_remove.txt');
        
        $filename = 'notification_remove.txt';
        exec('touch ' . $filename);
        file_put_contents($filename, $removed_hardware);

        $command = 'python ./webhook.py ' . 'remove';

        exec($command);  
      }
      
      Send_Email($body_mail);
      $mastermind->update_id_assets();

      echo "O email foi enviado com sucesso!";
    }
    catch (Exception $e) {
      echo "Ocorreu um erro no envio do email: " . $e->getMessage();
    }
  } else {
		echo "Nada a enviar :(" . "\n";	
  } 
	
