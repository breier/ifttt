:local apiurl "https://smart-api.example.com";
:local eth1name "ether1";

:local eth1mac [/interface ethernet get [find name=$eth1name] mac-address];

/tool fetch url="$apiurl/ddns" \
http-method=post output=user \
http-data="{\"macAddress\":\"$eth1mac\"}";
