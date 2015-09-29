Zend TenStreet Application
=======================

Introduction
------------
This is a simple CRUD application for storing, viewing, relaying and submitting commerical driver applications to a 3rd Party SOAP Web Service at TenStreet via a publicly accessible REST endpoint. 

Installation
------------
Import the SQL file in the /data directory to create the Database Schema.

Details
------------
The application uses [ZF-OAuth2](https://github.com/zfcampus/zf-oauth2) for API access control and authorization, and [ZFCUSer](https://github.com/ZF-Commons/ZfcUser) and [BjYAuthorize](https://github.com/bjyoungblood/BjyAuthorize) for front-end access control. 

Web server setup
----------------
The APPLICATION_ENV environment variable is used to determine development vs production environments.  
Using the SetEnv directive, add the APPLICATION_ENV variable to your VirtualHost configuration for Apache.  

Controllers
----------------
### Application - Index
List, explore and submit lead information to API.

### Application - REST
REST Endpoint providing interface between data sender and TenStreet API.

### TenStreet - SoapClient
SOAP Client for data submission to TenStreet API.
