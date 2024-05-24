# Cronjob OCS - Notificação de E-mail

Esse cronjob é um job executado de tempos em tempos, atualmente de 1 em 1 minuto, para que a verificação de informações e, se necessário, o envio de notificações via e-mail seja executado dentro do OCS Inventory.

Ele executa uma chamada para a API Kubernetes que realiza o comando necessário.

## Arquivo `Dockerfile`

Antes de subir a aplicação é preciso fornecer alguns dados no arquivo dockerfile na hora de gerar a image que será usada no cronjob.

### Variáveis de ambiente

```
env:
  CLUSTER_SERVER=
  CERTIFICATE_AUTHORITY=
  CLUSTER_NAME=
  NAMESPACE_NAME=
  CONTEXT_NAME=
  USER_NAME=
  USER_CLIENT_CERTIFICATE=
  USER_CLIENT_KEY=
  CONTAINER_NAME=
  POD_NAME=
  POD_NAMESPACE=
```

Em que:
- `CLUSTER_SERVER`: IP do server master node.
- `CERTIFICATE_AUTHORITY`: Certificado de autorização.
- `CLUSTER_NAME`: Nome do cluster.
- `NAMESPACE_NAME`: Nome do namespace padrão.
- `CONTEXT_NAME`: Nome do context.
- `USER_NAME`: Usuário com permissões.
- `USER_CLIENT_CERTIFICATE`: Certificado do usuário.
- `USER_CLIENT_KEY`: Chave de acesso do usuário.
- `CONTAINER_NAME`: Nome do container do OCS.
- `POD_NAME`: Prefixo do pod do OCS, leia a OBS 2 no final do arquivo.
- `POD_NAMESPACE`: Nome do namespace do qual o OCS faz parte.

todas essas informações, com exceção das três últimas, podem ser recuperadas no arquivo `config` no master node ou na ENV `KUBECONFIG` do Gitlab deste repósitorio.

### Comandos úteis
- `kubectl get cronjob nodecronjob`: Verifica se o cronjob foi criado.
- `kubectl get pods`: Mostra os pods, inclusive os que foram criados pelo cronjob, o número de pods referentes ao cronjob depende das configurações passadas no arquivo [cronjob-mail.yaml](https://gl.idc.ufpa.br/ocs_inventory-ufpa/2.8/-/tree/master/templates)
- `kubectl logs $nome_do_pod`: Mostra os logs de um pod para verificar se o comando foi executado com sucesso.
- `kubectl delete cronjob nodecronjob`: Deleta o cronjob. Caso o cronjob seja deletado todos os jobs e pods referentes a ele também serão deletados e a criação de novos jobs/pods será cessada.

OBS: O arquivo cronjob-mail.yaml para alteração de intervalos de tempo de execução e images carregadas está no diretorio de [templates](https://gl.idc.ufpa.br/ocs_inventory-ufpa/2.8/-/tree/master/templates).

OBS 2: Apenas a ENV "POD_NAME" não é uma configuração exata. Dada a forma como o cluster foi configurado, os pods podem ter seus nomes alterados caso um venha a cair, por isso, POD_NAME recebe apenas o prefixo do pod desejado, visto que esse prefixo sempre se mantém. O trabalho de procurar e recuperar o nome correto do pod é feito pelo próprio job.