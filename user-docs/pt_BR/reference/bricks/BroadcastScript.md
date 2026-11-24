---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Quando você receber](../../../media/bricks/when-you-receive-__2.png)

Com este bloco, o seu objeto fica escutando uma certa mensagem e começa o script quando a mensagem chega. É útil para os objetos conversarem entre si e para dizer a eles quando fazer alguma coisa.

![broadcast2](../../../media/bricks/broadcast2.png)

**Por que mandar mensagens?**

Com mensagens, objetos diferentes de uma cena podem conversar e agir juntos. Um objeto manda uma mensagem para todos os outros objetos, e os objetos que recebem podem reagir ou responder a essa mensagem.

Essas mensagens **não aparecem** na tela! Se você quer mostrar mensagens na tela, use os blocos da categoria “Aparências”!

**Como funciona?**

- *Objeto que envia:* o objeto que envia começa a conversa com um bloco *“Transmitir”*. Esse bloco manda uma mensagem para todos os outros objetos da cena (o cenário também).
- *Objeto que recebe:* o objeto que recebe é aquele que escuta as mensagens e responde. Ele usa o bloco *“Quando você receber”* para perceber quando uma certa mensagem chega.
- *O que acontece com a mensagem:* quando o objeto que recebe ganha a mensagem, ele faz todos os blocos que estão embaixo do bloco *“Quando você receber”*. Assim você pode decidir o que deve acontecer quando uma certa mensagem chega.
