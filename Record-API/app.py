from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import redis
import os
from datetime import datetime
import logging

app = Flask(__name__)
Logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

DB_HOST = os.getenv('DB_HOST', 'mysql-container')
DB_PORT = os.getenv('DB_PORT', '3306')
DB_NAME = os.getenv('DB_NAME', 'messagedb')
DB_USER = os.getenv('DB_USER', 'root')
DB_PASSWORD = os.getenv('DB_PASSWORD', 'password')

#Conecção com o MySQL
app.config['SQLALCHEMY_DATABASE_URI'] = f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

#Iniciação
db = SQLAlchemy(app)
migrate = Migrate(app, db)


redis_host = os.getenv('REDIS_HOST', 'redis')
redis_client = redis.Redis(host=redis_host, port=6379, db=0)

# Mensagem para o BD
class Message(db.Model):
    __tablename__ = 'messages'
    
    id = db.Column(db.Integer, primary_key=True)
    sender = db.Column(db.String(80), nullable=False)
    receiver = db.Column(db.String(80), nullable=False)
    content = db.Column(db.Text, nullable=False)
    timestamp = db.Column(db.DateTime, nullable=False, default=datetime.utcnow)
    status = db.Column(db.String(20), default='unread')

    def to_dict(self):
        return {
            'id': self.id,
            'sender': self.sender,
            'receiver': self.receiver,
            'content': self.content,
            'timestamp': self.timestamp.isoformat(),
            'status': self.status
        }


@app.route('/health', methods=['GET'])
def health_check():
    try:
        
        db.session.execute('SELECT 1')
        
        redis_client.ping()
        return jsonify({'status': 'healthy'}), 200
    except Exception as e:
        logger.error(f"Health check failed: {str(e)}")
        return jsonify({'status': 'unhealthy', 'error': str(e)}), 500

@app.route('/messages', methods=['POST'])
def store_message():
    try:
        data = request.get_json()
        
        if not all(k in data for k in ['sender', 'receiver', 'content']):
            return jsonify({'error': 'Missing required fields'}), 400
        
        new_message = Message(
            sender=data['sender'],
            receiver=data['receiver'],
            content=data['content'],
            status='unread'
        )
        
        db.session.add(new_message)
        db.session.commit()
        
        redis_key = f"message:{new_message.id}"
        redis_client.hmset(redis_key, new_message.to_dict())
               
        redis_client.publish('new_messages', str(new_message.id))
        
        return jsonify(new_message.to_dict()), 201
    
    except Exception as e:
        db.session.rollback()
        logger.error(f"Error storing message: {str(e)}")
        return jsonify({'error': 'Failed to store message'}), 500


@app.route('/messages', methods=['GET'])
def get_messages():
    try:
        
        cached_messages = []
        for key in redis_client.scan_iter("message:*"):
            cached_msg = {k.decode('utf-8'): v.decode('utf-8') for k, v in redis_client.hgetall(key).items()}
            cached_messages.append(cached_msg)
        
        if cached_messages:
            return jsonify({'messages': cached_messages, 'source': 'cache'}), 200
        

        messages = Message.query.order_by(Message.timestamp.desc()).limit(100).all()
        result = [msg.to_dict() for msg in messages]
        
        
        for msg in messages:
            redis_key = f"message:{msg.id}"
            redis_client.hmset(redis_key, msg.to_dict())
        
        return jsonify({'messages': result, 'source': 'database'}), 200
    
    except Exception as e:
        logger.error(f"Error retrieving messages: {str(e)}")
        return jsonify({'error': 'Failed to retrieve messages'}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)