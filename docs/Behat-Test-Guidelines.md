### A new Feature must be a new feature file. 

- **A feature file** should only test exactly **one feature**. 
- You might even want to split your feature into multiple different feature files.
- Try to keep Scenarios as **small** and possible. No need to check everything in one way too large scenario. 
- Just test what has to be checked. No need to check the same thing twice.
- Keep the processing time as small as possible. When a scenario just checks for a button it does not need to have a background that creates multiple database entries that are not used. 
- the most important thing: Try to **test for every possible scenario**.

### Prevent those timing issues
Behat tests tend to check for elements before they are loaded from time to time. To prevent such timing issues always wait for objects you reference to be loaded. But only **use wait in combination with conditions**. <br/>
Don't use something like `I wait for 1000 milliseconds`. Maybe this will increase the testing time unnecessarily or might not even be long enough for the object to load. Just make use of the wait function with a timeout and a condition. This condition defines what has to be fulfilled to continue.

Some examples.

- When using stuff like `I am on '/app'` append `I wait for the page to be loaded`

- For checks like `I should be on '/app'` prepend `I wait for the page to be loaded`

- When there is some async javascript, because, for example, you clicked on some buttons, you might want to use something like `I wait for AJAX to finish`. Or it might be even better if you just wait for the object you need with something like `I wait for the element '#id' to contain 'message'`, `I wait for the element '#id' to be visible/exist/...`

### Additionally 
Try not to use simple methods like `I should see 'message'`. Better check that the correct element contains the correct content with methods like `The element '#id' should contain 'message'`. 

***

### Our context files WIP
The context files define the glue code for our Behat features. Try to reuse existing functionality. Refactor it. Try to make them easier usable. Prevent methods with default parameters. Already implicitly include the wait to get rid of those explicit wait calls in the features. Split the files. Adapt the behat config to use the correct context files. We need to get rid of all this copy/pasted duplicated code.