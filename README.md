Lead Capture Application
=======================

Introduction
------------
This is a ORM drived CRUD application for storing, viewing, relaying and submitting commerical driver applications to different 3rd Party Web Services and/or email depending on client account.

Installation
------------
Import the SQL file in the /data directory to create the Database Schema.

Details
------------
The application uses [ZF-OAuth2](https://github.com/zfcampus/zf-oauth2) for API access control and authorization, and [ZFCUSer](https://github.com/ZF-Commons/ZfcUser) and [BjYAuthorize](https://github.com/bjyoungblood/BjyAuthorize) for front-end access control and [Doctrine2](https://github.com/doctrine/doctrine2) for ORM mapping. 

Web server setup
----------------
The APPLICATION_ENV environment variable is used to determine development vs production environments.  
Using the SetEnv directive, add the APPLICATION_ENV variable to your VirtualHost configuration for Apache.  

Controllers
----------------
### Application - Dashboard
Homepage

### Lead - Lead
CRUD operations and submit lead information to API/Email.

### Account - Account
CRUD operations for Client Accounts

### API - Api
CRUD Operations for APIs

### Application - REST
REST Endpoint providing interface between data sender and Dashboard.

### Event - Event
CRUD Operations and ORM Entities for eventing functionality.

### TenStreet - SoapClient
SOAP Client for data submission to TenStreet API.

### User - User
ORM Entities and configuration for user access and role capability.
