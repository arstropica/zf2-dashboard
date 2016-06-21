Lead KPI Dashboard Application
=======================

Introduction
------------
This application presents an (S)CRUD administrative interface for storing, viewing, relaying and submitting commerical driver applications to different 3rd Party Web Services and/or email depending on client account.

Installation
------------
a. Generate the schema

    ./vendor/bin/doctrine-module orm:schema-tool:create

b. Validate the schema against the generate database structure

    ./vendor/bin/doctrine-module orm:validate-schema


Details
------------
The application uses [ZF-OAuth2](https://github.com/zfcampus/zf-oauth2) for API access control and authorization, and [ZFCUSer](https://github.com/ZF-Commons/ZfcUser) and [BjYAuthorize](https://github.com/bjyoungblood/BjyAuthorize) for front-end access control, [Doctrine2](https://github.com/doctrine/doctrine2) for ORM mapping and [Elastica](https://github.com/ruflin/Elastica) for variable field searching. 

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

* Lead - main CRUD Controller
* Attribute - SCRUD Controller for lead attributes
* Import - Import Controller handling lead import wizard
* REST - REST Endpoint Controller
* Services - Central API Operations Controller
* Email - Email Leads Controller (List only) 
* TenStreet - TenStreet Leads Controller (List/Submit)
* Source - Leads Referrer Management Controller (Edit/Merge)
* Report - Leads Advanced Search Controller (Search/Result/Export)

### Account - Account
CRUD operations for Client Accounts

### Agent - ElasticSearch
Implementation of ElasticSearch Query and Filter functionality for data search.

### API - Api
CRUD Operations for APIs

### Application - REST
REST Endpoint providing interface between data sender and Dashboard.

### Event - Event
CRUD Operations and ORM Entities for eventing functionality.

### TenStreet - SoapClient
SOAP Client for data submission to TenStreet API.

### Email - SendMail
SendMail Service for email functionality.

### Reports - Search
Provides UI && business logic for Search Agent

### WebWorks - CURL
CURL client for data submission to WebWorks API.

### User - User
ORM Entities and configuration for user access and role capability.
