---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Difundir](../../../media/bricks/broadcast2-1.png)

Envia uma mensagem a outras partes do teu projeto, dizendo-lhes para começarem os seus próprios scripts. É como fazer um anúncio que todos ouvem.

**Porque é que se envia mensagens de difusão?**

Com as mensagens, os objetos de uma cena conseguem comunicar e interagir entre si. Um objeto pode enviar uma mensagem a todos os outros objetos, e os objetos que a recebem podem reagir a essa mensagem.

Estas mensagens **não são mostradas** no ecrã! Se quiseres mostrar mensagens, usa os blocos da categoria “Aspeto”!

**Como funciona?**

- *Objeto que envia:* O objeto que envia começa a comunicação com um bloco *“Difundir”*. Este bloco envia uma mensagem a todos os outros objetos da cena (e também ao fundo).
- *Objeto que recebe:* O objeto que recebe é o que ouve e reage às mensagens. Usa o bloco *“Quando receber”* para detetar quando chega uma mensagem específica.
- *Tratamento da mensagem:* Quando o objeto que recebe recebe a mensagem, executa todos os blocos que estão por baixo do bloco *“Quando receber”*. Assim, podes definir um comportamento ou ações próprias que acontecem quando essa mensagem chega.
