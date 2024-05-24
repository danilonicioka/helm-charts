require('dotenv').config();
const k8s = require('@kubernetes/client-node');

async function execCommand() {
  //------Configurações necessárias para executar os comandos-------
  const kubeconfig = `
  apiVersion: v1
  clusters:
  - cluster:
      certificate-authority-data: ${process.env.CERTIFICATE_AUTHORITY}
      server: ${process.env.CLUSTER_SERVER}
    name: ${process.env.CLUSTER_NAME}
  contexts:
  - context:
      cluster: ${process.env.CLUSTER_NAME}
      namespace: ${process.env.NAMESPACE_NAME}
      user: ${process.env.USER_NAME}
    name: ${process.env.CONTEXT_NAME}
  current-context: ${process.env.CONTEXT_NAME}
  kind: Config
  preferences: {}
  users:
  - name: ${process.env.USER_NAME}
    user:
      client-certificate-data: ${process.env.USER_CLIENT_CERTIFICATE}
      client-key-data: ${process.env.USER_CLIENT_KEY} 
  `

  const NAMESPACE = process.env.POD_NAMESPACE;
  const POD_NAME = process.env.POD_NAME;
  const CONTAINER_NAME = process.env.CONTAINER_NAME;
  const COMMAND = ['php73', '/usr/share/ocsinventory-reports/ocsreports/require/components/cron_mailer.php'];

  //--------------------Fim das configurações----------------------//
  
  // Busca pelo nome correto do pod no namespace
  async function findPod(k8sApi, namespace, podTarget) {
    try {
      const podsRes = await k8sApi.listNamespacedPod(namespace);
      for (const item of podsRes.body.items) {
        if (item.metadata.name.includes(podTarget)) {
          return item.metadata.name;
        }
      }
      throw new Error(`Pod ${podTarget} não encontrado em ${namespace}`);
    } catch (err) {
      throw err;
    }
  }

  // Função principal para executar o comando
  async function main() {
    const kc = new k8s.KubeConfig();
    // Inicializando as configurações do Kubernetes
    kc.loadFromString(kubeconfig);
    //kc.loadFromDefault();

    const k8sApi = kc.makeApiClient(k8s.CoreV1Api);

    try {
      const podName = await findPod(k8sApi, NAMESPACE, POD_NAME);
      if (podName) {
        const exec = new k8s.Exec(kc);
        // Executando o comando no pod encontrado
        await exec.exec(
          NAMESPACE,
          podName,
          CONTAINER_NAME,
          COMMAND,
          process.stdout,
          process.stderr,
          process.stdin
        );
        console.log('Comando executado com sucesso!');
      }
    } catch (err) {
      console.error('Erro ao executar o comando:', err);
    }
  }

  main();
}

execCommand();
