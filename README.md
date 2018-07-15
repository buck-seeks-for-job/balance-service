# Test task for IQOption

The project requires docker and docker compose to be installed on local machine. 
The project has been tested on Ubuntu 16.04 and correct work on other systems is not guaranteed.

To start you need to clone project then run startup script:
```bash
git clone git@github.com:buck-seeks-for-job/balance-service.git
cd balance-service
build/run.sh
```
After that 3 workers are running each in its own container along with postgres and rabbitMQ.
You can test the correctness of the system by running special script that puts some test data into queue.
```bash
build/generate-test-data.sh
```
This script works correctly only when all necessary containers are running

To see how tests are passing you can run special script:
```bash
build/run-tests.sh 
```
It is important that to run tests you need all php-cli image to be build.
But it is also possible to run tests from the host machine. To do that you need php7.2 or later to be installed on host machine with all necessary extensions.
You also need to provide all necessary permissions to vendor and var directories.
