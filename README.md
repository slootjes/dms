# Document Management System

## Concept

### File Structure

Put all your pdf documents in a directory structure like this:

#### With recipient

`/{yyyy}/{MM}/{yyyy-MM-dd} - {sender} - {recipient} - {subject}.pdf`

Example:
`2020/04/2020/2020-04-12 - Sender - Receipient - Subject.pdf`

#### With specific recipient

`/{yyyy}/{MM}/{yyyy-MM-dd} - {sender} - {subject}.pdf`

Example:
`2020/04/2020/2020-04-12 - Sender - Subject.pdf`

### Recommendation
For best results make sure your documents contain actual text (using ocr if needed) to improve reliability.

### Application

The structure as is works really well on it's own to quickly find documents.
Most operating systems are capable of finding files using their built-in search engine. 

To make searching easier and allow searching specific documents the app can index your documents 
in Elasticsearch and provides an easy to use tool to search, view and download these documents.

## Requirements

- PHP ^7.4
- Elasticsearch ^7.6 with Ingest Attachment Processor Plugin

## Installation

### Docker

Recommended installation is by using the provider Dockerfile. 

#### Build
`docker build -t dms .`

#### Run
`docker run -d -p 1337:80 -e APP_ENV="prod" -e APP_SECRET="{something_random}" -e ELASTICSEARCH_HOSTS="{elasticsearch_host}" -v {path_to_your_documents}:/documents --name dms dms`

### Docker Compose

See example setup combined with Elasticsearch and Kibana (optional) in docker-compose.yaml.

### Commands

Once the app is up and running using Docker you can run the commands like this:

Setup index: `docker exec dms php /var/www/html/bin/console document:setup`

Index your documents: `docker exec dms php /var/www/html/bin/console document:index`

## Troubleshooting

### Installing Elasticsearch Ingest Attachment Processor Plugin

If you have issues installing the plugin (eg: because of java permission issues), the easiest thing 
to do is to manually install the plugin after spinning up the Elasticsearch container:

`docker exec -i -t elasticsearch /usr/share/elasticsearch/bin/elasticsearch-plugin install ingest-attachment`

Make sure you have mapped the `/usr/share/elasticsearch/plugins` folder from the container to a local volume.
After this is done you must restart the container and you should be good to go.

## Disclaimer

You can use this software as-is without any warranty.
You are responsible for securing your devices and software yourself.
