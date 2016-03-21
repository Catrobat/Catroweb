# Authentication to the system
> How to register and login to the system

## Registration of a new user
> 

Given The HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And The POST parameters:

| Name | Value |
| --- | --- |
| registrationUsername | newuser |
| registrationPassword | registrationpassword |
| registrationEmail | test@mail.com |
| registrationCountry | at |
   
And We assume the next generated token will be &quot;`rrrrrrrrrrr`&quot;
 
When The Request is invoked
 
Then The returned json object will be:
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

Given The registration problem &quot;`&lt;problem&gt;`&quot;
 
When Such a Request is invoked
 
Then The returned json object will be:
```json
{
  "statusCode": "<errorcode>",
  "answer": "<answer>",
  "preHeaderMessages": ""
}
```
 
 

### Examples
| problem | errorcode | answer |
| --- | --- | --- |
| no password given | 602 | The password is missing. |

---

## Retrieve the upload token of a user
> 

Given The HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And The POST parameters:

| name | value |
| --- | --- |
| registrationUsername | Catrobat |
| registrationPassword | 12345 |
   
When The Request is invoked
 
Then The returned json object will be:
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

Given The HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/checkToken/check.json |
   
And The POST parameters:

| Name | Value |
| --- | --- |
| username | Catrobat |
| token | cccccccccc |
   
When The Request is invoked
 
Then The returned json object will be:
```json
{
  "statusCode": 200,
  "answer": "ok",
  "preHeaderMessages": "  \n"
}
```
 
And The response code will be &quot;`200`&quot;
 
 


---

## Troubleshooting
> 

Given The check token problem &quot;`&lt;problem&gt;`&quot;
 
When Such a Request is invoked
 
Then The returned json object will be:
```json
{
  "statusCode": "<errorcode>",
  "answer": "<answer>",
  "preHeaderMessages": ""
}
```
 
And The response code will be &quot;`&lt;httpcode&gt;`&quot;
 
 

### Examples
| problem | errorcode | answer | httpcode |
| --- | --- | --- | --- |
| invalid token | 601 | Authentication of device failed: invalid auth-token! | 401 |

---

  
# Background

Given there are users:

| name | password | token |
| --- | --- | --- |
| Catrobat | 12345 | cccccccccc |
| User1 | vwxyz | aaaaaaaaaa |
   
 