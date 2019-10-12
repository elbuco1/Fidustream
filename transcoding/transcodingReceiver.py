import pika
import json
import transcoder

# Rabbit receiver that is used to receive messages from symfony project through a rabbit queue
# Runs the corresponding transcoder function and acknowledge it to rabbit
# When the process ends, sends a confirmation message (success/fail) to symfony project
# through another rabbit queue
# Multiples receiver can be run at the same time
# The task dispatching between the receivers is made according to round robin algorithm
# The receiver uses pika which is the Rabbitmq python library
# This receiver is used for mp4 and webm video transcoding

# Connects to the rabbit adress
# Creates a queue which is durable (if server crashes the messages are saved)
# and uses acknowledge to indicate if a message has been consummed or not, so it can be removed
# from the queue 
# Run the receiver
def main():
    connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
    channel = connection.channel()
    channel.queue_declare(queue='transcoding_queue', durable=True)
    channel.exchange_declare(exchange='transcode_exchange', type='direct', durable=True)
    channel.basic_consume(callback, queue='transcoding_queue', no_ack=False)
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()

# When a process ends, it calls this function
# This function uses an other queue to send a confirmation message to symfony project
def send(videoId, success, errorMessage):
    message = {
        "id" : videoId,
        "success" : success,
        "error" : errorMessage
        }
        
    connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
    channel = connection.channel()
    channel.queue_declare(queue='confirmation_queue', durable=True)
    channel.exchange_declare(exchange='transcode_exchange', type='direct', durable=True)
    channel.basic_publish(exchange='transcode_exchange', \
    routing_key='confirmation_queue', body=json.dumps(message).encode())
    
    connection.close()

# The callback is called when the receiver gets a message from the Rabbit queue
# The message contains the video id, the path of the original video and the 
# destination paths for both the mp4  and webm versions of the original video
# It calls the function from the transcoder file and calls the send function at the end of the process
def callback(ch, method, properties, body):
    message = json.loads(body.decode())

    id = message["id"]
    originalPath = message["originalPath"]
    mp4Path = message["mp4Path"]
    webmPath = message["webmPath"]
    thumbnailPath = message["thumbnailPath"]
    transcodeSuccess = 1
    errorMessage = "La conversion a échoué en raison de l'erreur suivante: "
    try:
        transcoder.transcodeToMp4(originalPath, mp4Path)
        transcoder.transcodeToWebm(originalPath, webmPath)
        transcoder.extractThumbnail(originalPath, thumbnailPath)
    except Exception as e :
        transcodeSuccess = 0
        errorMessage += str(e)
        print(errorMessage)

    except KeyboardInterrupt as e:
        transcodeSuccess = 0
        errorMessage += str(e)
        print(errorMessage)

    if transcodeSuccess :
        print(" [x] transcoding has  succeed")
        
    send(message["id"], transcodeSuccess, errorMessage)
    ch.basic_ack(delivery_tag = method.delivery_tag)

if __name__ == "__main__":
    main()