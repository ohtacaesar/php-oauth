FROM python:3-alpine3.7

RUN apk add --no-cache --virtual .build-deps gcc libc-dev linux-headers \
    &&  pip install flask uwsgi \
    &&  apk del --purge .build-deps

COPY wsgi.py /app/

WORKDIR /app

CMD ["uwsgi", "--http", ":9000", "--wsgi-file", "wsgi.py"]

EXPOSE 9000
