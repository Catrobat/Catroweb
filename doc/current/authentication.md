# Authentication to the system
> How to register and login to the system

## Registration of a new user
> 

Given I have the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And I use the POST parameters:

| Name | Value |
| --- | --- |
| registrationUsername | newuser |
| registrationPassword | registrationpassword |
| registrationEmail | test@mail.com |
| registrationCountry | at |
   
And We assume the next generated token will be &quot;rrrrrrrrrrr&quot;
 
When I invoke the Request
 
Then I will get the json object:
```json
{
  "token": "rrrrrrrrrrr",
  "statusCode": 201,
  "answer": "Registration successful!",
  "preHeaderMessages": ""
}
```
 
 


---

## Troubleshooting
> 

Given There is a registration problem &lt;problem&gt;
 
When I invoke the Request
 
Then I will get the json object:
 
 

### Examples
| problem | errorcode | answer |
| --- | --- | --- |
| no password given | 602 | The password is missing. |

---

## Retrieve the upload token of a user
> 

Given I have the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And I use the POST parameters:

| name | value |
| --- | --- |
| registrationUsername | Catrobat |
| registrationPassword | 12345 |
   
When I invoke the Request
 
Then I will get the json object:
```json
{
  "token": "cccccccccc",
  "statusCode": 200,
  "preHeaderMessages": ""
}
```
 
 


---

## Checking a given token for its validity
> 

Given I have the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/checkToken/check.json |
   
And I use the POST parameters:

| username | Catrobat |
| --- | --- |
| token | cccccccccc |
   
When I invoke the Request
 
Then I will get the json object:
```json
{
  "statusCode": 200,
  "answer": "ok",
  "preHeaderMessages": "  \n"
}
```
 
And The response code will be &quot;200&quot;
 
 


---

## Troubleshooting
> 

Given There is a check token problem &lt;problem&gt;
 
When I invoke the Request
 
Then I will get the json object:
 
And The response code will be &quot;&lt;httpcode&gt;&quot;
 
 

### Examples
| problem | errorcode | answer | httpcode |
| --- | --- | --- | --- |
| invalid token | 601 | Authentication of device failed: invalid auth-token! | 401 |

---

  