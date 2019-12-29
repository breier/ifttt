# SmartAPI
Breier Services for SmartAPI

## Wake On LAN
A server should use the POST /ddns endpoint at least every 20 days.
So its information won't be expired for remote access.
The remaining information is stored in the DDNS_HOSTS env variable in a JSON format.
Here goes an example of host configuration:
```JSON
{"01-23-45-AB-CD-EF":{"user":"admin","password":"admin","port":22}}
```
The API will use SSH to connect to this host and spawn the following command:
```SHELL
/system script run wol_pcd
```
