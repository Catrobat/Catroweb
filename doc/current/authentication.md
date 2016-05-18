# Authenticate to the system
> How to register and login to the system

## Registration of a new user
> 

Given the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And the POST parameters:

| Name | Value |
| --- | --- |
| registrationUsername | newuser |
| registrationPassword | registrationpassword |
| registrationEmail | test@mail.com |
| registrationCountry | at |
   
And we assume the next generated token will be "`rrrrrrrrrrr`"
 
When the Request is invoked
 
Then the returned json object will be:
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

Given the registration problem "`<problem>`"
 
When such a Request is invoked
 
Then the returned json object will be:
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

Given the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/loginOrRegister/loginOrRegister.json |
   
And the POST parameters:

| name | value |
| --- | --- |
| registrationUsername | Catrobat |
| registrationPassword | 12345 |
   
When the Request is invoked
 
Then the returned json object will be:
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

Given the HTTP Request:

| Method | POST |
| --- | --- |
| Url | /pocketcode/api/checkToken/check.json |
   
And the POST parameters:

| Name | Value |
| --- | --- |
| username | Catrobat |
| token | cccccccccc |
   
When the Request is invoked
 
Then the returned json object will be:
```json
{
  "statusCode": 200,
  "answer": "ok",
  "preHeaderMessages": "  \n"
}
```
 
And the response code will be "`200`"
 
 


---

## Troubleshooting
> 

Given the check token problem "`<problem>`"
 
When such a Request is invoked
 
Then the returned json object will be:
```json
{
  "statusCode": "<errorcode>",
  "answer": "<answer>",
  "preHeaderMessages": ""
}
```
 
And the response code will be "`<httpcode>`"
 
 

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
   
 