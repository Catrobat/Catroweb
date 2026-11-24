---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![Envoyer à tous et attendre](../../../media/bricks/brodcast_wait_brick3.png)

Envoie un message aux autres parties de ton projet, puis attend qu’elles aient fini leur travail avant de continuer. C’est comme demander à un ami de faire quelque chose et attendre qu’il ait fini avant de continuer.

**Pourquoi envoyer des messages ?**

Avec les messages, les différents objets d’une scène peuvent communiquer entre eux. Un objet peut envoyer un message à tous les autres objets, et les objets qui le reçoivent peuvent alors réagir à ce message.

Ces messages **ne s’affichent pas** à l’écran ! Si tu veux afficher des messages, utilise les briques de la catégorie « Apparences » !

**Comment ça marche ?**

- *Objet qui envoie :* L’objet qui envoie commence la communication avec un bloc *« Envoyer à tous »*. Ce bloc envoie un message à tous les autres objets de la scène (aussi à l’arrière-plan).
- *Objet qui reçoit :* L’objet qui reçoit est celui qui écoute les messages et y répond. Il utilise le bloc *« Quand je reçois »* pour savoir quand un message précis arrive.
- *Traitement du message :* Quand l’objet reçoit le message, il exécute tous les blocs qui se trouvent sous le bloc *« Quand je reçois »*. Comme ça, tu peux choisir ce qui se passe quand un message précis arrive.
