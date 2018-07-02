# Symfony-Football-League-API

Load random data to database:
````
php bin/console doctrine:fixtures:load
````

***Authorization***

````
    POST http://YOUR_DOMAIN/v1/login
    @params:
        *username: admin
        *password: admin
 ````       
 ````       
    POST http://YOUR_DOMAIN/v1/refresh-token
    @params:
        *refresh_token: <refresh_token>
    @headers:
        Authorization: Bearer <access_token>
 ````
 
 ***League***   
    
 ````      
    GET http://YOUR_DOMAIN/v1/league
    @params:
        limit: 10
        offset: 0
    @headers:
        Authorization: Bearer <access_token>
 ````       
 ````    
    DELETE http://YOUR_DOMAIN/v1/league/{id}
    @params:
        *{id}: <league_id>
    @headers:
        Authorization: Bearer <access_token>
        
````

***Team***   
     
````        
    GET http://YOUR_DOMAIN/v1/team
    @params:
        league_id: <league_id>
        limit: 10
        offset: 0
    @headers:
        Authorization: Bearer <access_token>
 ````       
 ````    
    POST http://YOUR_DOMAIN/v1/team
    @params:
        *name: <name>
        *strip: <strip>
        *league_id: <league_id>
    @headers:
        Authorization: Bearer <access_token>
 ````       
 ````    
    PUT http://YOUR_DOMAIN/v1/team/{id}
    @params:
        *{id}: <team_id>
        name: <name>
        strip: <strip>
        league_id: <league_id>
    @headers:
        Authorization: Bearer <access_token>
````
````
* - required fields
````