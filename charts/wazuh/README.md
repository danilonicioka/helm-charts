# Wazuh

Wazuh é uma plataforma de segurança que unifica os recursos XDR e SIEM. É um Sistema de Detecção de Intrusão baseado em host. Assim, seus principais recursos incluem: detecção de ameaças, solução de monitoramento de identificação, resposta a incidente e conformidade.

# Agente Wazuh

A solução Wazuh é baseada no agente Wazuh, que monitora sistemas, detecta ameaças e aciona respostas automáticas conforme necessário. Mais especificamente, eles aprimoram rootkits e malware, bem como anomalias suspeitas.

## Detecção de Vulnerabilidade

Os agentes do Wazuh extraem dados de inventário de software e os enviam para seus servidores. Aqui, ele é comparado com bancos de dados de vulnerabilidades e exposições comuns (CVE) atualizados continuamente. Como resultado, esses agentes encontrarão e identificarão qualquer software vulnerável.

# Instalação do agente

[Mais informações de instalação do agente](https://documentation.wazuh.com/current/installation-guide/wazuh-agent/index.html)

## Linux

**OBS: É necessário estar logado como usuário root para executar os seguintes comandos**

1. Instale a chave GPG:

```
curl -s https://packages.wazuh.com/key/GPG-KEY-WAZUH | gpg --no-default-keyring --keyring gnupg-ring:/usr/share/keyrings/wazuh.gpg --import && chmod 644 /usr/share/keyrings/wazuh.gpg
```

2. Adicione o repositório:

```
echo "deb [signed-by=/usr/share/keyrings/wazuh.gpg] https://packages.wazuh.com/4.x/apt/ stable main" | tee -a /etc/apt/sources.list.d/wazuh.list
```

3. Atualize as informações do pacote:

```bash
apt-get update
```

4. Deploy do agente Wazuh:

```bash
WAZUH_MANAGER="wazuh.csic.ufpa.br" WAZUH_MANAGER_PORT="30001" WAZUH_REGISTRATION_PORT="30002" WAZUH_REGISTRATION_PASSWORD="password" WAZUH_AGENT_NAME="teste2" apt-get install wazuh-agent
```

Em que:
- WAZUH_AGENT_NAME: é o nome do agente que aparecerá no dashboard. **OBS: Por padrão, será utilizado o nome do computador, então, caso seja o nome desejado, basta remover essa variável do comando** 

5. Habilite e inicie o serviço do agente Wazuh.

    5.1. Systemd
    ```bash
    systemctl daemon-reload
    systemctl enable wazuh-agent
    systemctl start wazuh-agent
    ```

    5.2. SysV init
    ```bash
    update-rc.d wazuh-agent defaults 95 10
    service wazuh-agent start
    ```

6. Para verificar se o agente Wazuh está funcionando.

```bash
sudo journalctl -u wazuh-agent.service
```

7. Remover repositório do wazuh das atualizações para evitar que o agente tenha uma [versão maior](https://documentation.wazuh.com/current/upgrade-guide/compatibility-matrix/index.html) que a do manager

```bash
sed -i "s/^deb/#deb/" /etc/apt/sources.list.d/wazuh.list
apt-get update
```

## Windows

**Execute o PowerShell como administrador.**

1. Execute o comando no terminal do windows (PowerShell).
```
Invoke-WebRequest -Uri https://packages.wazuh.com/4.x/windows/wazuh-agent-4.3.4-1.msi -OutFile wazuh-agent-4.3.4.msi; ./wazuh-agent-4.3.4.msi /q WAZUH_MANAGER="wazuh.csic.ufpa.br" WAZUH_MANAGER_PORT="30001" WAZUH_REGISTRATION_PORT="30002" WAZUH_REGISTRATION_PASSWORD="Pk95*3KL" WAZUH_AGENT_NAME="teste2"
```

Em que:
- WAZUH_AGENT_NAME: é o nome do agente que aparecerá no dashboard. **OBS: Por padrão, será utilizado o nome do computador, então, caso seja o nome desejado, basta remover essa variável do comando**

2. inicie o agente

```
NET START WazuhSvc
```

## Ansible
[Instalar agente Wazuh](https://documentation.wazuh.com/current/deployment-options/deploying-with-ansible/guide/install-wazuh-agent.html)

```
cd /etc/ansible/roles/
```

```
sudo git clone https://github.com/wazuh/wazuh-ansible.git
```

Editar o playbook /etc/ansible/roles/wazuh-ansible/playbooks/wazuh-agent.yml como no exemplo abaixo:

```
---
- hosts: <your wazuh agents hosts>
  become: yes
  become_user: root
  roles:
    - ../roles/wazuh/ansible-wazuh-agent
  vars:
    wazuh_managers:
      - address: wazuh.csic.ufpa.br
        port: 30001
        protocol: tcp
        api_port: 30003
        api_proto: 'http'
        api_user: ansible
        max_retries: 5
        retry_interval: 5
    wazuh_agent_authd:
      registration_address: wazuh.csic.ufpa.br
      enable: true
      port: 30002
      ssl_agent_ca: null
      ssl_auto_negotiate: 'no'
    wazuh_agent_enrollment:
      manager_address: 'wazuh.csic.ufpa.br'
      port: 30002
      authorization_pass_path: /var/ossec/etc/authd.pass
```

OBS:
- **Hosts:** alterar para o grupo de servidores que será instalado o wazuh

Executar o playbook:

```
cd /etc/ansible/roles/wazuh-ansible/playbooks/
```

```
ansible-playbook wazuh-agent.yml -b -K
```

# Remover agente

## Linux

```
apt-get remove --purge wazuh-agent
```

1. Systemd

```
systemctl disable wazuh-agent
systemctl daemon-reload
```

2. SysV init

```
update-rc.d -f wazuh-agent remove
```

## Windows

```
msiexec.exe /x wazuh-agent-4.4.1-1.msi /qn
```

# Helm Chart para Wazuh

A base para criação deste helm chart foi obtida a partir da ferramenta [Helmify](https://github.com/arttor/helmify), pois, no site oficial do Wazuh, é disponibilizada apenas a instalação em um cluster por meio do [Kustomize](https://kustomize.io/). Visto isso, abaixo serão listados apenas as principais modificações feitas.

OBS: Na branch [kustomize-install](https://gl.idc.ufpa.br/csic/wazuh/-/tree/kustomize-install), os arquivos necessários estão disponíveis para utilizar esse método.

## Values.yaml

### Files:

As modificações estão basicamente concentradas no arquivos `values.yaml`, a qual contia o conteúdo de todos os arquivos utilizados, sejam certificados ou arquivos de configuração. Portanto, para facilitar a visualização e organização do projeto, os arquivos foram colocados nas pasta `files` na seguinte estrutura:

```
values.yaml
files
  |---certs
      |---admin-key.pem
      |---admin.pem
      |---cert.pem
      |---dashboard-key.pem
      |---dashboard.pem
      |---filebeat-key.pem
      |---filebeat.pem
      |---key.pem
      |---node-key.pem
      |---node.pem
      |---root-ca.pem
  |---conf
      |---dashboard
          |---opensearch_dashboards.yaml
      |---indexer
          |---internal_users.yaml
          |---opensearch.yaml
      |---wazuh
          |---master.conf
          |---worker.conf
```

Como consequência, os arquivos que incluíam o conteúdo desses arquivos também foram modificados para obter o conteúdo a partir dos arquivos, ou seja, basicamente todos os arquivos de secret (scrt-\*) e de config map (cm-\*)

#### Arquivos de configuração

Por padrão, o wazuh não deixa habilitado o modulo de vulnerabilidades, mas, caso seja utilizado, pode-se habilitar antes de fazer o deploy nos arquivos files/wazuh/*.conf. Obs: neste repositório, essa modifição já foi feita.

```
 <vulnerability-detector>
      <enabled>yes</enabled>
```

#### Certificados

O wazuh possui scripts para facilitar a criação de certificados temporários para testes, os quais podem ser encontrados na pasta `files/certs`

### Credenciais:

Para modificar as credenciais utilizadas de forma mais fácil, foram concentradas no campo `cred` no início do arquivos `values.yaml`.

```
cred:
  api:
    password: 3x@mpl3.*-
    username: wazuh-wui
  authd:
    password: password
  cluster:
    key: 123a45bc67def891gh23i45jk67l8mn9
  dashboard:
    password: kibanaserver
    username: kibanaserver
  indexer:
    password: SecretPassword
    username: admin
```

Deve-se ter cuidado ao alterar as credenciais, pois isso pode fazer todo o wazuh não funcionar corretamente, alguns dos casos notados foram:
- `api password não segura`: quando foi utilizada uma senha sem caractere especial, por exemplo, a api não iniciou por acusar senha não segura.
- `user hashes`: ao alterar a senha dos usuários (kibanaserver e admin, etc), deve-se alterar a hash correspondente no arquivo `files/conf/indexer/internal_users.yml` ou o wazuh não funcionará corretamente.

### Recursos

Dependendo da infraestrutura - quantos agentes, nós, etc -, o wazuh precisará de [bastante recurso para funcionar normalmente](https://groups.google.com/u/2/g/wazuh/c/ItDiTwjy85Y?pli=1).

Os campos `resources` e `storage` foram alterados nas configurações dos pods e pvc para que o wazuh funcionasse normalmente.

```
resources:
      requests:
        cpu: 500m
        memory: 1Gi

...

requests:
        storage: 10Gi
```