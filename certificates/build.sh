#!/bin/bash

openssl req -x509 -newkey rsa:1024 -keyout phpuush.pem -out phpuush.pem -days 36500 -nodes
openssl x509 -in phpuush.pem -inform pem -out phpuush.der -outform der