![brodcast_wait_brick3](../../media/bricks/brodcast_wait_brick3.png)

Sends a message to other parts of your project and then waits until they finish their tasks before moving on. It’s like asking a friend to do something and waiting for them to finish before you continue.

**Why send broadcast messages?**

With messages you can communicate and interact between different objects within a scene. It allows one object to send a message to all other objects, and the receiving objects can then react or respond to that message.

These messages are **not displayed** on the screen! If you want to display messages, use the bricks in the “Looks” category!

**How does it work?**

- *Sender object:* The sender object starts the communication by using a *“Broadcast”* block. This block is used to send a message to all other objects in the scene (also the background).
- *Receiving object:* The receiving object is the one that listens for and responds to messages. It uses the *“When you receive”* block to detect when a specific message is being received.
- *Message Handling:* When the receiving object gets the message, it will execute all the blocks that are beneath the *“When you receive”* block. This allows you to define custom behavior or actions that occur when a particular message is received.
