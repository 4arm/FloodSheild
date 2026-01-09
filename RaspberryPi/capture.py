import cv2
import time
import os
import requests
from datetime import datetime

# --- CONFIGURATION ---
GCP_VM_IP = "34.124.166.142" 
GCP_UPLOAD_URL = f"http://{GCP_VM_IP}/upload.php"
INTERVAL = 30 

# --- TELEGRAM CONFIG ---
TELEGRAM_TOKEN = "8316505148:AAEcXWAa4PPWkJKtnzNZqxsT76hkR0p7YV0"
TELEGRAM_CHAT_ID = "671087884"

def send_telegram_alert(image_path, status_text):
    """Sends a photo and message to Telegram."""
    try:
        # 1. Send the text message
        msg_url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendMessage"
        msg_data = {"chat_id": TELEGRAM_CHAT_ID, "text": f"⚠️ FLOOD SHIELD ALERT\n{status_text}"}
        requests.post(msg_url, data=msg_data, timeout=5)

        # 2. Send the captured photo
        photo_url = f"https://api.telegram.org/bot{TELEGRAM_TOKEN}/sendPhoto"
        with open(image_path, 'rb') as f:
            photo_data = {"chat_id": TELEGRAM_CHAT_ID}
            photo_file = {"photo": f}
            requests.post(photo_url, data=photo_data, files=photo_file, timeout=10)
            
        print("Telegram notification sent successfully.")
    except Exception as e:
        print(f"Failed to send Telegram alert: {e}")

def get_camera():
    for index in [1, 0, 2]:
        cap = cv2.VideoCapture(index, cv2.CAP_V4L2)
        if cap.isOpened():
            print(f"Camera successfully opened at index {index}")
            return cap
        cap.release()
    return None

def main():
    hog = cv2.HOGDescriptor()
    hog.setSVMDetector(cv2.HOGDescriptor_getDefaultPeopleDetector())
    
    cap = get_camera()
    if cap is None:
        print("Error: Could not open camera.")
        return

    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    local_temp = os.path.join(BASE_DIR, "local_temp")
    if not os.path.exists(local_temp): os.makedirs(local_temp)

    print("--- Flood Shield Active with Telegram Alerts ---")

    try:
        while True:
            for _ in range(5):
                ret, frame = cap.read()
            
            if ret:
                frame = cv2.resize(frame, (640, 480))
                (rects, weights) = hog.detectMultiScale(frame, winStride=(4, 4), padding=(8, 8), scale=1.05)
                
                human_found = len(rects) > 0
                ts = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

                for (x, y, w, h) in rects:
                    cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 0, 255), 2)
                
                status = "HUMAN DETECTED" if human_found else "NORMAL"
                cv2.putText(frame, f"{ts} | {status}", (10, 30), 
                            cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)

                filepath = os.path.join(local_temp, "latest.jpg")
                cv2.imwrite(filepath, frame)
                
                # --- ACTION: TELEGRAM NOTIFICATION ---
                if human_found:
                    print(f"[{ts}] 🚨 Human found! Sending Telegram alert...")
                    send_telegram_alert(filepath, f"Detection at {ts}: Human spotted in monitoring zone.")

                # Upload to GCP VM
                try:
                    with open(filepath, 'rb') as f:
                        files = {'fileToUpload': (f'img_{int(time.time())}.jpg', f, 'image/jpeg')}
                        requests.post(GCP_UPLOAD_URL, files=files, timeout=5)
                    print(f"[{ts}] Uploaded to Dashboard. Human: {human_found}")
                except Exception as e:
                    print(f"GCP Upload failed: {e}")
            
            time.sleep(INTERVAL)
            
    except KeyboardInterrupt:
        print("\nStopping...")
    finally:
        if cap: cap.release()

if __name__ == "__main__":
    main()
