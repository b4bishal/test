import cv2
import json
import os
import numpy as np
from datetime import datetime
import time

def load_enrollments():
    """Load existing enrollments from JSON file"""
    if os.path.exists('enrollments.json'):
        with open('enrollments.json', 'r') as f:
            return json.load(f)
    return {'students': []}

def save_enrollments(data):
    """Save enrollments to JSON file"""
    with open('enrollments.json', 'w') as f:
        json.dump(data, f, indent=4)

def capture_face_data(name):
    """Capture face images from webcam"""
    # Try different camera indices for Mac compatibility
    cap = None
    for index in [0, 1]:
        cap = cv2.VideoCapture(index)
        if cap.isOpened():
            print(f"Camera found at index {index}")
            break
        cap.release()

    if not cap or not cap.isOpened():
        print("Error: Could not open camera")
        return None

    # Set camera properties for better compatibility
    cap.set(cv2.CAP_PROP_FRAME_WIDTH, 640)
    cap.set(cv2.CAP_PROP_FRAME_HEIGHT, 480)
    cap.set(cv2.CAP_PROP_FPS, 30)

    # Give camera time to initialize (important for Mac)
    print("Initializing camera...")
    time.sleep(2)

    # Warm up camera by reading a few frames
    for i in range(10):
        cap.read()

    # Load Haar Cascade for face detection
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    print(f"\nEnrolling: {name}")
    print("Press SPACE to capture photo, ESC to cancel, ENTER when done")

    captured_images = []
    capture_count = 0

    while True:
        ret, frame = cap.read()
        if not ret:
            print("Warning: Failed to grab frame")
            time.sleep(0.1)
            continue

        # Convert to grayscale for face detection
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        faces = face_cascade.detectMultiScale(gray, 1.3, 5)

        # Draw rectangle around faces
        for (x, y, w, h) in faces:
            cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 255, 0), 2)
            cv2.putText(frame, f"Captured: {capture_count}/5", (x, y-10), 
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 255, 0), 2)

        # Display instructions
        cv2.putText(frame, f"Enrolling: {name}", (10, 30), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
        cv2.putText(frame, "SPACE: Capture | ENTER: Done | ESC: Cancel", (10, 60), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 1)

        cv2.imshow('Face Enrollment', frame)

        key = cv2.waitKey(1) & 0xFF

        # Space bar - capture image
        if key == 32 and len(faces) > 0:
            face_img = frame.copy()
            captured_images.append(face_img)
            capture_count += 1
            print(f"Captured image {capture_count}")

            if capture_count >= 5:
                print("Minimum 5 images captured. Press ENTER to finish.")

        # Enter - finish enrollment
        elif key == 13 and capture_count >= 3:
            break

        # ESC - cancel
        elif key == 27:
            print("Enrollment cancelled")
            cap.release()
            cv2.destroyAllWindows()
            return None

    cap.release()
    cv2.destroyAllWindows()

    if capture_count < 3:
        print("Not enough images captured. Need at least 3 images.")
        return None

    return captured_images

def save_face_images(name, images):
    """Save captured face images to disk"""
    # Create directory for face images
    face_dir = 'face_data'
    if not os.path.exists(face_dir):
        os.makedirs(face_dir)

    # Create subdirectory for this person
    person_dir = os.path.join(face_dir, name.replace(' ', '_').lower())
    if not os.path.exists(person_dir):
        os.makedirs(person_dir)

    image_paths = []
    for idx, img in enumerate(images):
        filename = f"{name.replace(' ', '_').lower()}_{idx+1}.jpg"
        filepath = os.path.join(person_dir, filename)
        cv2.imwrite(filepath, img)
        image_paths.append(filepath)

    return image_paths

def enroll_student():
    """Main enrollment function"""
    print("=" * 50)
    print("SMART ATTENDANCE SYSTEM - FACE ENROLLMENT")
    print("=" * 50)

    # Get student name
    name = input("\nEnter student name: ").strip()

    if not name:
        print("Name cannot be empty!")
        return

    # Load existing enrollments
    data = load_enrollments()

    # Check if name already exists
    for student in data['students']:
        if student['name'].lower() == name.lower():
            response = input(f"'{name}' is already enrolled. Re-enroll? (y/n): ")
            if response.lower() != 'y':
                print("Enrollment cancelled")
                return
            else:
                data['students'].remove(student)
                break

    # Capture face data
    print("\nStarting webcam...")
    print("Please ensure good lighting and look at the camera")
    print("Capture at least 3-5 different angles of your face")

    images = capture_face_data(name)

    if images is None:
        return

    # Save images to disk
    image_paths = save_face_images(name, images)

    # Create enrollment record
    enrollment = {
        'id': len(data['students']) + 1,
        'name': name,
        'enrollment_date': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'image_count': len(images),
        'image_paths': image_paths
    }

    # Add to data
    data['students'].append(enrollment)

    # Save to JSON
    save_enrollments(data)

    print(f"\n✓ Successfully enrolled {name}!")
    print(f"  - ID: {enrollment['id']}")
    print(f"  - Images captured: {len(images)}")
    print(f"  - Data saved to: enrollments.json")
    print(f"  - Face images saved to: face_data/{name.replace(' ', '_').lower()}/")

if __name__ == "__main__":
    try:
        enroll_student()
    except KeyboardInterrupt:
        print("\n\nEnrollment interrupted by user")
    except Exception as e:
        print(f"\nError: {e}")
        import traceback
        traceback.print_exc()