# Estrutura Dockerfiles

Os dockerfiles para construção das image pelo CI do Gitlab estão dividos em 2 arquivos. Perceba que o arquivo "Dockerfile-old" é um antigo Dockerfile usado antes da separação em dois arquivos e que não é mais necessário a menos que seja para fins de backup se for preciso.

## Dockerfile-base-image
O arquivo "Dockerfile-base-image" é o responsável por gerar a image base para o próximo Dockerfile, ele foi separado para agilizar o build do CI, já que existem muitas libs para serem instaladas e que raramente são modificadas.

Em caso de atualização do "Dockerfile-base-image" gere as alterações e suba manualmente uma image buildada localmente no Container Registry do Gitlab. Para tal, execute os comandos na ordem que se segue:

---
- Build a image.
```
docker build -t ucr.idc.ufpa.br/ocs_inventory-ufpa/2.8:base-image .
```

- Faça login no repo com credências validas.
```
docker login ucr.idc.ufpa.br
```

- Faça um push da image buildada.
```
docker push ucr.idc.ufpa.br/ocs_inventory-ufpa/2.8:base-image
```
---

A Tag foi convencionada em "base-image", e ela é usada no build do próximo Dockerfile, perceba que ela substituirá a antiga no Container Registry e caso opite por alterá-la, para manter um backup temporário, altere também no "Dockerfile".

O Gitlab não permite subir uma image com um nome diferente do que a URL já definida no Container Registry. Com isso, todas as images desse tipo aparecerão dentro de "2.8" no Container Registry e serão identificadas únicamente por suas TAGs.

## Dockerfile
O arquivo "Dockerfile" é o arquivo que gera uma image no CI para ser usada posteriormente no deploy, ele deve sempre permanecer com este nome e neste diretório pois o CI busca exatamente por esses nomes quando inicia um Job. No mais, este arquivo também gera uma image que é armazenada no Container Registry com uma TAG especificada pelo CI no arquivo ".gitlab-ci.yml".