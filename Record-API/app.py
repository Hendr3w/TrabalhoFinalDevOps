from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
from functools import wraps
import jwt
import datetime

app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = 'mysql+pymysql://user:pass@mysql-container:3306/messagedb'
app.config['SECRET_KEY'] = 'supersecret'
db = SQLAlchemy(app)


class Message(db.Model):
    __tablename__ = 'messages'
    id = db.Column(db.Integer, primary_key=True)
    sender_id = db.Column(db.Integer, nullable=False)
    receiver_id = db.Column(db.Integer, nullable=False)
    content = db.Column(db.Text, nullable=False)
    timestamp = db.Column(db.DateTime, default=datetime.datetime.utcnow)


class MessageService:
    @staticmethod
    def create_message(sender_id, receiver_id, content):
        new_message = Message(
            sender_id=sender_id,
            receiver_id=receiver_id,
            content=content
        )
        db.session.add(new_message)
        db.session.commit()
        return new_message

def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        token = request.headers.get('Authorization')
        if not token:
            return jsonify({'message': 'Token is missing!'}), 401
        
        try:
            data = jwt.decode(token.split()[1], app.config['SECRET_KEY'], algorithms=["HS256"])
        except:
            return jsonify({'message': 'Token is invalid!'}), 401
        
        return f(*args, **kwargs)
    return decorated

@app.route('/message', methods=['POST'])
@token_required
def add_message():
    data = request.get_json()
    
    message = MessageService.create_message(
        data['userIdSend'],
        data['userIdReceive'],
        data['message']
    )
    
    return jsonify({"ok": True}), 201

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)