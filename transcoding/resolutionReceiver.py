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
# This receiver is used for the video resolutions computing

# Connects to the rabbit adress
# Creates a queue which is durable (if server crashes the messages are saved)
# and uses acknowledge to indicate if a message has been consummed or not, so it can be removed
# from the queue 
# Run the receiver
def main():
    connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
    channel = connection.channel()
    channel.queue_declare(queue='resolution_queue', durable=True)
    channel.exchange_declare(exchange='transcode_exchange', type='direct', durable=True)
    channel.basic_consume(callback, queue='resolution_queue', no_ack=False)
    print(' [*] Waiting for messages. To exit press CTRL+C')
    channel.start_consuming()


# When a process ends, it calls this function
# This function uses an other queue to send a confirmation message to symfony project
# The message contains the maximum resolution computed by the process
def send(videoId,maxResolution, success, errorMessage):
    message = {
        "id" : videoId,
        "success" : success,
        "resolution" : maxResolution,
        "error" : errorMessage
        }
        
    connection = pika.BlockingConnection(pika.ConnectionParameters('localhost'))
    channel = connection.channel()
    channel.queue_declare(queue='confirmationRes_queue', durable=True)
    channel.exchange_declare(exchange='resolution_exchange', type='direct', durable=True)
    channel.basic_publish(exchange='resolution_exchange', \
    routing_key='confirmationRes_queue', body=json.dumps(message).encode())
    
    connection.close()


# The callback is called when the receiver gets a message from the Rabbit queue
# The message contains a list of reolution computing to do.
# Each job contains the video id, the path of the original video, the destination directory and the 
# original extension video
# It calls the function from the transcoder file and calls the send function at the end of the process
def callback(ch, method, properties, body):
    jobs = json.loads(body.decode())
    transcodeSuccess = 1
    maxResolution = 0
    errorMessage = "Le calcul des résolutions de la vidéo a échoué en raison de l'erreur suivante: "
    mp4 = jobs[0]
    uid = mp4["id"]
    try:
        for job in jobs:
            videoId = str(job["id"])
            originalPath = job["originalPath"]
            directory = job["dir"]
            extension = job["ext"]            
            maxResolution = transcoder.computeResolution(originalPath, directory, videoId, extension)
     
    except Exception as e :
        transcodeSuccess = 0
        errorMessage += str(e)
        print(errorMessage)

    except KeyboardInterrupt as e:
        transcodeSuccess = 0
        errorMessage += str(e)
        print(errorMessage)
    if transcodeSuccess :
        print(" [x] transcoding has succeed")
    send(uid,maxResolution, transcodeSuccess, errorMessage)
    ch.basic_ack(delivery_tag = method.delivery_tag)


if __name__ == "__main__":
    main()