#!/bin/bash
#
# Script to keep the smart-api up-to-date with this host IP

API_URL="https://smart-api.example.com"

ETH1_NAME="ether1"
ETH1_MAC=$(ip link show dev ${ETH1_NAME} | grep link | awk '{print $2}')

curl --request PUT "${API_URL}/ddns" \
    --header 'Content-Type: application/json' \
    --data-raw "{\"macAddress\": \"${ETH1_MAC}\"}" \
    > /dev/null 2>&1
