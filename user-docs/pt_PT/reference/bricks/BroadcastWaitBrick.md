---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Difundir e esperar](../../../media/bricks/brodcast_wait_brick3.png)

Envia uma mensagem a outras partes do teu projeto e depois espera que terminem as suas tarefas antes de continuar. É como pedir a um amigo para fazer uma coisa e esperar que ele acabe antes de continuares.

**Porque é que se envia mensagens de difusão?**

Com as mensagens, os objetos de uma cena conseguem comunicar e interagir entre si. Um objeto pode enviar uma mensagem a todos os outros objetos, e os objetos que a recebem podem reagir a essa mensagem.

Estas mensagens **não são mostradas** no ecrã! Se quiseres mostrar mensagens, usa os blocos da categoria “Aspeto”!

**Como funciona?**

- *Objeto que envia:* O objeto que envia começa a comunicação com um bloco *“Difundir”*. Este bloco envia uma mensagem a todos os outros objetos da cena (e também ao fundo).
- *Objeto que recebe:* O objeto que recebe é o que ouve e reage às mensagens. Usa o bloco *“Quando receber”* para detetar quando chega uma mensagem específica.
- *Tratamento da mensagem:* Quando o objeto que recebe recebe a mensagem, executa todos os blocos que estão por baixo do bloco *“Quando receber”*. Assim, podes definir um comportamento ou ações próprias que acontecem quando essa mensagem chega.
