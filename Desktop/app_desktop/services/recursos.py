import sys
import os

def resource_path(path):
    if hasattr(sys, "_MEIPASS"):
        return os.path.join(sys._MEIPASS, path)
    return os.path.join(os.path.abspath("."), path)

def newSprit(name):
    return resource_path(f"assets/sprites/{name}")

def newSound(name):
    return resource_path(f"assets/sounds/{name}")
