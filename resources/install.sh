#!/bin/bash
touch /tmp/dependency_attestationcovid_in_progress
echo 0 > /tmp/dependency_attestationcovid_in_progress
echo "Launch install of attestationcovid dependancies"
echo "-- Updating repo..."
sudo apt-get update
echo 20 > /tmp/dependency_attestationcovid_in_progress
echo ""
echo "-- Installation of python3 and dependancies"
sudo apt-get install -y pdftk
echo 100 > /tmp/dependency_attestationcovid_in_progress
rm /tmp/dependency_attestationcovid_in_progress