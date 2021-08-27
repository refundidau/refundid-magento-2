#Magento2_Refundid
Refundid "Intant Refunds" module for Magento 2

note: this must be installed in conjunction with our Magento Integration - please contact us at info@refundid.com for more info on setup


## New Refundid Installation
This section outlines the steps to install the Afterpay plugin for the first time.

Note: [MAGENTO] refers to the root folder where Magento is installed.

- Download the Magento-Refundid plugin - Available as a .zip or tar.gz file from the Afterpay GitHub directory. 
- Unzip the file
- Copy the *'Magento'* folder to: *[MAGENTO]/app/code/* 
- Open Command Line Interface
- In CLI, run the below command to enable Afterpay module: *php bin/magento module:enable Refundid_CreditMemo*
- In CLI, run the Magento setup upgrade: *php bin/magento setup:upgrade*
- In CLI, run the Magento Dependencies Injection Compile: *php bin/magento setup:di:compile*
- Login to Magento Admin and navigate to System/Cache Management
- Flush the cache storage by selecting Flush Cache Storage