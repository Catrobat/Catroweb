---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![broadcast2](../../../media/bricks/broadcast2-1.png)

Manda uma mensagem para outras partes do seu projeto e diz para elas começarem os seus próprios scripts. É como dar um aviso que todo mundo escuta.

**Por que mandar mensagens?**

Com mensagens, objetos diferentes de uma cena podem conversar e agir juntos. Um objeto manda uma mensagem para todos os outros objetos, e os objetos que recebem podem reagir ou responder a essa mensagem.

Essas mensagens **não aparecem** na tela! Se você quer mostrar mensagens na tela, use os blocos da categoria “Aparências”!

**Como funciona?**

- *Objeto que envia:* o objeto que envia começa a conversa com um bloco *“Transmitir”*. Esse bloco manda uma mensagem para todos os outros objetos da cena (o cenário também).
- *Objeto que recebe:* o objeto que recebe é aquele que escuta as mensagens e responde. Ele usa o bloco *“Quando você receber”* para perceber quando uma certa mensagem chega.
- *O que acontece com a mensagem:* quando o objeto que recebe ganha a mensagem, ele faz todos os blocos que estão embaixo do bloco *“Quando você receber”*. Assim você pode decidir o que deve acontecer quando uma certa mensagem chega.
