---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Quand je reçois](../../../media/bricks/when-you-receive-__2.png)

Avec ce bloc, ton objet attend un message précis et lance le script quand il le reçoit. C’est pratique pour faire communiquer les objets et leur dire quand faire quelque chose.

![Envoyer à tous](../../../media/bricks/broadcast2.png)

**Pourquoi envoyer des messages ?**

Avec les messages, les différents objets d’une scène peuvent communiquer entre eux. Un objet peut envoyer un message à tous les autres objets, et les objets qui le reçoivent peuvent alors réagir à ce message.

Ces messages **ne s’affichent pas** à l’écran ! Si tu veux afficher des messages, utilise les briques de la catégorie « Apparences » !

**Comment ça marche ?**

- *Objet qui envoie :* L’objet qui envoie commence la communication avec un bloc *« Envoyer à tous »*. Ce bloc envoie un message à tous les autres objets de la scène (aussi à l’arrière-plan).
- *Objet qui reçoit :* L’objet qui reçoit est celui qui écoute les messages et y répond. Il utilise le bloc *« Quand je reçois »* pour savoir quand un message précis arrive.
- *Traitement du message :* Quand l’objet reçoit le message, il exécute tous les blocs qui se trouvent sous le bloc *« Quand je reçois »*. Comme ça, tu peux choisir ce qui se passe quand un message précis arrive.
