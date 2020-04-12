# Document Management System (work in progress)

## Concept

### File Structure

Put all your pdf documents in a directory structure like this:

/{year}/{month}/{date} - {sender} - {recipient} - {subject}.pdf

or if there is no specific recipient:

/{year}/{month}/{date} - {sender} - {subject}.pdf

Make sure your pdf documents contain actual text (using ocr if needed) to improve reliability.

### Application

The app can index your documents in Elasticsearch and provides an easy to use tool to search, view and download your documents.

## Installation

### Docker

Recommended installation is by using the provider Dockerfile. 

### Docker Compose

See example setup combined with Elasticsearch and Kibana in docker-compose.yaml.

### Commands

Once the app is up and running using Docker you can run the commands like this:

Setup index: `docker exec dms php /var/www/html/bin/console document:setup`

Index your documents: `docker exec dms php /var/www/html/bin/console document:index`

### Disclaimer

You can use this software as-is without any warranty.
You are responsible for securing your devices and software yourself.
