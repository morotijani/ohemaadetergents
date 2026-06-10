from PIL import Image
import numpy as np

def process_logo():
    path = r"c:\xampp\htdocs\ohemaadetergents\public\assets\img\logo.png"
    img = Image.open(path).convert("RGBA")
    data = np.array(img)
    
    # The generated image background is usually off-white or slightly gray
    # Let's consider anything close to white as background
    r, g, b, a = data[:,:,0], data[:,:,1], data[:,:,2], data[:,:,3]
    
    # Calculate distance to pure white
    # If it's very bright (all channels > 230), make it transparent
    mask = (r > 230) & (g > 230) & (b > 230)
    
    data[:,:,3][mask] = 0
    
    # Find bounding box
    non_empty_columns = np.where(data[:,:,3].max(axis=0) > 0)[0]
    non_empty_rows = np.where(data[:,:,3].max(axis=1) > 0)[0]
    
    if len(non_empty_columns) > 0 and len(non_empty_rows) > 0:
        # Add a tiny bit of padding (e.g. 10 pixels)
        pad = 10
        min_x = max(0, min(non_empty_columns) - pad)
        min_y = max(0, min(non_empty_rows) - pad)
        max_x = min(data.shape[1], max(non_empty_columns) + 1 + pad)
        max_y = min(data.shape[0], max(non_empty_rows) + 1 + pad)
        
        cropped_data = data[min_y:max_y, min_x:max_x]
        img_cropped = Image.fromarray(cropped_data)
        img_cropped.save(path)
        print("Logo cropped and background removed successfully.")
    else:
        print("Could not find logo bounding box.")

if __name__ == '__main__':
    process_logo()
