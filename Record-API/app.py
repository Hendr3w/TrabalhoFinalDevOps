from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import redis
import os

app = Flask(__name__)



redis_host = os.getenv('REDIS_HOST', 'localhost')
redis_client = redis.Redis(host=redis_host, port=6379, db=0)

