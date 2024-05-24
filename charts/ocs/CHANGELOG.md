# 1.7 - 2024-03-18
## Changelog: Modificado
- Atualizado chaves de acesso ao cluster nos cronjob devido a mudança de arquitetura.

# 1.6 - 2023-11-14
## Changelog: Adicionado
- Adicionado serviço de nodePort na porta 30080 para contornar problemas com envio de e-mail, o envio de notificações discord continua executando normalmente.

# 1.5 - 2023-09-29
## Changelog: Modificado
- Cronjob refatorado e reestruturado, yamls configurados para pull de images do repo.

# 1.4 - 2023-06-14
## Changelog: Adicionado
- Notificação no canal do discord via webhook

# 1.3 - 2023-05-15
## Changelog: Modificado
- cron_mailer pede a atualização das tabelas apenas se conseguir enviar o E-mail.

# 1.2 - 2021-10-15
## Changelog: Adicionado
- Arquivo .html que especifica o estilo do CSS utilizado no E-mail a ser enviado.

# 1.1 - 2021-10-10
## Changelog: Adicionado
- Função filter_array_cells().
- Método get_storages() adicionado novamente.
- Bug Fixes

## Changelog: Removido
- Método get_disks() removido.


# 1.0 - 2021-09-17
## Changelog: Adicionado
- Método get_html_info_addition() adicionado no código-fonte ComponentsNotification.php: Método que gera o html para os componentes 
adicionados.
- Método get_html_info_removed() adicionado no código-fonte ComponentsNotification.php:  Método que gera o html para os componentes
removidos.
- Método get_disks().
- Função update_id_assets() Adicionado no código-fonte ComponentesNotification.php: Método que atualiza a tabela cache de UserID no banco de dados. 

## Changelog: Modificado
- Métodos refatorados: get_cpus(), get_memories(), get_monitors(), get_videos(), get_storages();
- Método get_html_general_information modificado: Método desmambrado em dois.
- Código-fonte send.php: adicionado trechos para a geração de um arquivo de log. Para registro no envio de e-mails.
- Código-fonte cron_mailer.php: Adicionado a chamada de método update_id_assets(). 

# 0.9 - 2021-08-31
## Changelog: Adicionado
- Método get_cpus() no código-fonte ComponentsNotification.php
- Função db_connect() no código-fonte ComponentsNotification.php	

## Changelog: Modificado
- Função Send_Email(): utilização das credenciais de email fornecidas via interface gráfica
- Arquivo cron_mailer.php: Adicionado a chamada do método get_cpus()

## Changelog: Removido
- Método db_connect()

# 0.8 - 2021-08-29
## Changelog: Adicionado
- Método get_storages() no código-fonte ComponentsNotification.php

## Changelog: Modificado
- Classe Components_Notification.php. Mudança de nome para adequar-se ao PHP code style. Agora chama-se ComponentsNotification.php
- Arquivo cron_mailer.php: Adicionado a chamada do método get_storages();

# 0.7 - 2021-08-26
## Changelog: Adicionado
- Método get_videos() no código-fonte Componentes_Notification.php.

# 0.6 - 2021-08-24
## Changelog: Adicionado
- Método get_monitors() no código-fonte Components_Notification.php.

## Changelog: Modificado
- Arquivo cron_mailer.php: Adicionado a chamada do método get_monitors().

## Changelog: Modificado
- Método get_html_general_information() no código-fonte Components_Notification.php: Adaptação do método para haver a criação unificada de e-mail para todos os componentes. 
- Classe Components_Notification.php: Dois campos de instância adicionados, html_part_addition e html_part_remove.

# 0.5/Stable_Version - 2021-08-20
## Changelog: Modificado
- Método get_html_general_information(): estruturado para adaptar-se a nova tabela auxiliar criada.
- Método get_memories(): Tomada de decisão para identificação de componentes removidos implantada.

# 0.4 - 2021-08-10
## Changelog: Modificado
- Método get_html_general_information(): Nova lógica implantada e modificação da assinatura do método.

# 0.3 - 2021-08-10
## Changelog: Removido
- Método verify_db_table() no código-fonte Components_Notificacation.php: O método tornou-se desnecessário para o decorrer do desenvolvimento.

## Changelog: Modificado
- Método get_memories(): O método está temporariamente inativo para envio de e_mail. 

# 0.2 - 2021-08-04
## Changelog: Adicionado
- Método verify_db_table() no código-fonte Components_Notification.php.

## Changelog: Modificado
- Método get_memories(). Desmembrou-se uma estrutura condicional (if) a fim de especializar a tomada
de decisão para duas situações: Adição e a remoção de componentes de hardware.

# 0.1 - 2021-08-03
## Changelog: Adicionado
- Método get_memories() no código-fonte Components_Notification
- Arquivo Components_Notification.php para coleta de informações do banco de dados.
- Arquivo cron_mailer.php para a automatização do envio de emails.
- Arquivo send.php para o envio de emails.

# 0.0 - 2021-08-01
## Changelog: Modificado
- Arquivo CHANGELOG.md adicionado para fins de registros de modificações no código-fonte.
- Arquivo README.md adicionado para explicitar, em linhas gerais, os motivos do  desenvolvimento do projeto.
