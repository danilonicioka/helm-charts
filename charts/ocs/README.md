# Helm Chart para OCS Inventory NG (Enhanced)

Baseado em [ocsinventory-docker-image-ctic](https://gl.idc.ufpa.br/ocs_inventory-ufpa/2.8/-/tree/alternative-install), o qual é uma versão melhorada do OCS Inventory NG.

Utiliza o chart do [mysql](https://artifacthub.io/packages/helm/wso2/mysql) para o banco de dados do OCS.

## Arquivo `values.yaml`

Antes de subir a aplicação, é preciso modificar alguns campos no arquivo `values.yaml` para garantir a comunicação correta dos serviços.

### Variáveis de ambiente

```
env:
  OCS_DB_NAME: ocsweb
  OCS_DB_SERVER: ocs-mysql
  OCS_DB_USER: ocsuser
  OCS_DB_PASS: "123"
  TZ: America/Belem
```

Em que:
- `OCS_DB_NAME`: nome do banco de dados no mysql para o OCS
- `OCS_DB_SERVER`: nome do service do mysql
- `OCS_DB_USER`: usuário do banco de dados
- `OCS_DB_PASS`: senha para acessar o banco de dados
- `TZ`: timezone

### Persistência

```
persistence:
  enabled: true
  storageClass: ""
  perlcomdata: 
    existingClaim: ""
    storage: "100Mi"
  ocsreportsdata: 
    existingClaim: ""
    storage: "100Mi"
  varlibdata: 
    existingClaim: ""
    storage: "100Mi"
  httpdconfdata: 
    existingClaim: ""
    storage: "100Mi"
```

Em que:
- `enabled: true`: habilita a persistência de dados do OCS
- `storageClass`: nome do SC
- `existingClaim`: nome do PVC
- `storage`: tamanho do volume

### Ingress

```
ingress:
  enabled: true
  className: nginx
  host: example.com
  ocs:
    path: /
    pathType: Prefix
```

Em que:
- `className`: nome do ingressClass
- `host`: url para acessar OCS
- `path`: caminho para acessar OCS
- `pathType`: indica que a rota deve corresponder ao prefixo indicado em path, ignora `/` após o prefixo, por exemplo. Ver mais na [documentação](https://kubernetes.io/docs/concepts/services-networking/ingress/#path-types)

### Credenciais para ter acesso à imagem

```
imageCredentials:
  enabled: true
  registry: example.com
  username: OCS_ACCESS_TOKEN
  password: token
```

Em que:
- registry: url do registry
- username: nome de um usuário com acesso ao registry ou o nome do access token
- password: senha do usuário ou o valor do access token

### MySQL

Para facilitar a configuração do banco de dados, pode-se incluir campos para serem repassados para o subchart do mysql. Exemplo:

```
mysql:
  env:
    rootPass: "12345"
    user: ocsuser
    pass: "123"
    db: ocsweb
    timezone: America/Belem
  persistence:
    enabled: true
    storageClass: ""
    accessMode: ReadWriteOnce
    data:
      existingClaim: ""
      size: "5Gi"
    bareos:
      existingClaim: ""
      size: "5Gi"
```

Em que:
- `rootPass`: senha do root do banco de dados
- `user`: usuário do banco de dados
- `pass`: senha do usuário
- `db`: nome do banco de dados a ser usado pelo ocs
- `acessMode`: permissão de acesso aos dados persistidos

OBS: o usuário, a senha e o banco de dados definidos para o OCS devem ser as mesmas definidas aqui para haver comunicação