# buat isi db via python

from app import app, db, InventoryItem

# Sample data
new_items = [
    {"sku": "SKU-1001", "name": "Heavy Duty Pallet Jack", "stock_level": 15, "price": 349.99},
    {"sku": "SKU-1002", "name": "Industrial Shelving Unit", "stock_level": 8, "price": 899.50},
    {"sku": "SKU-1003", "name": "Safety High-Vis Vest", "stock_level": 120, "price": 14.99},
    {"sku": "SKU-1004", "name": "Steel-Toe Work Boots (Size 10)", "stock_level": 45, "price": 89.99},
    {"sku": "SKU-1005", "name": "Packaging Tape (Bulk - 50 Rolls)", "stock_level": 200, "price": 45.00},
    {"sku": "SKU-1006", "name": "Bubble Wrap Roll (500ft)", "stock_level": 30, "price": 55.25},

    {"sku": "SKU-1007", "name": "Electric Forklift Battery", "stock_level": 6, "price": 2499.99},
    {"sku": "SKU-1008", "name": "Warehouse LED Light Panel", "stock_level": 75, "price": 39.95},
    {"sku": "SKU-1009", "name": "Hydraulic Lift Table", "stock_level": 12, "price": 1299.00},
    {"sku": "SKU-1010", "name": "Barcode Scanner Wireless", "stock_level": 55, "price": 119.49},
    {"sku": "SKU-1011", "name": "Industrial Label Printer", "stock_level": 18, "price": 399.99},
    {"sku": "SKU-1012", "name": "Anti-Fatigue Floor Mat", "stock_level": 90, "price": 24.99},
    {"sku": "SKU-1013", "name": "Warehouse Safety Helmet", "stock_level": 140, "price": 18.75},
    {"sku": "SKU-1014", "name": "Rechargeable Flashlight", "stock_level": 65, "price": 22.40},
    {"sku": "SKU-1015", "name": "Industrial Cleaning Cart", "stock_level": 20, "price": 279.00},
    {"sku": "SKU-1016", "name": "Heavy Duty Extension Cord", "stock_level": 110, "price": 34.99},
    {"sku": "SKU-1017", "name": "Shrink Wrap Machine", "stock_level": 4, "price": 1850.00},
    {"sku": "SKU-1018", "name": "Pallet Rack Beam", "stock_level": 70, "price": 72.30},
    {"sku": "SKU-1019", "name": "Warehouse Rolling Ladder", "stock_level": 14, "price": 499.95},
    {"sku": "SKU-1020", "name": "Industrial Fan 30-inch", "stock_level": 25, "price": 210.80},
    {"sku": "SKU-1021", "name": "Heavy Duty Storage Bin", "stock_level": 180, "price": 16.50},
    {"sku": "SKU-1022", "name": "Handheld Inventory Terminal", "stock_level": 11, "price": 799.99},
    {"sku": "SKU-1023", "name": "Dock Leveler Plate", "stock_level": 7, "price": 1450.00},
    {"sku": "SKU-1024", "name": "Industrial Workbench", "stock_level": 16, "price": 620.45},
    {"sku": "SKU-1025", "name": "Plastic Shipping Crate", "stock_level": 95, "price": 28.99},
    {"sku": "SKU-1026", "name": "Warehouse First Aid Kit", "stock_level": 50, "price": 64.99},
    {"sku": "SKU-1027", "name": "Cable Management Sleeve", "stock_level": 130, "price": 12.25},
    {"sku": "SKU-1028", "name": "Portable Air Compressor", "stock_level": 13, "price": 340.00},
    {"sku": "SKU-1029", "name": "Industrial Tool Chest", "stock_level": 9, "price": 950.00},
    {"sku": "SKU-1030", "name": "Thermal Shipping Labels", "stock_level": 300, "price": 19.99},
    {"sku": "SKU-1031", "name": "RFID Inventory Tags (Pack 100)", "stock_level": 150, "price": 74.50},
    {"sku": "SKU-1032", "name": "Warehouse Traffic Cone", "stock_level": 85, "price": 11.99},
    {"sku": "SKU-1033", "name": "Steel Reinforced Gloves", "stock_level": 160, "price": 17.80},
    {"sku": "SKU-1034", "name": "Pneumatic Nail Gun", "stock_level": 22, "price": 189.60},
    {"sku": "SKU-1035", "name": "Industrial Dehumidifier", "stock_level": 5, "price": 1340.75},
    {"sku": "SKU-1036", "name": "Mobile Packing Station", "stock_level": 10, "price": 780.00},
    {"sku": "SKU-1037", "name": "Heavy Duty Ratchet Straps", "stock_level": 140, "price": 29.99},
    {"sku": "SKU-1038", "name": "Warehouse Clock System", "stock_level": 18, "price": 155.00},
    {"sku": "SKU-1039", "name": "Inventory Clipboard Set", "stock_level": 250, "price": 8.75},
    {"sku": "SKU-1040", "name": "Industrial Cart Wheels", "stock_level": 320, "price": 13.20},
    {"sku": "SKU-1041", "name": "Forklift Safety Mirror", "stock_level": 40, "price": 58.90},
    {"sku": "SKU-1042", "name": "Cold Storage Thermal Jacket", "stock_level": 35, "price": 124.50},
    {"sku": "SKU-1043", "name": "Warehouse Exit Sign LED", "stock_level": 60, "price": 27.99},
    {"sku": "SKU-1044", "name": "Industrial Tape Dispenser", "stock_level": 145, "price": 9.99},
    {"sku": "SKU-1045", "name": "Heavy Load Platform Dolly", "stock_level": 17, "price": 265.40},
    {"sku": "SKU-1046", "name": "Portable Generator 5000W", "stock_level": 6, "price": 1150.00},
    {"sku": "SKU-1047", "name": "Warehouse Security Camera", "stock_level": 28, "price": 310.99},
    {"sku": "SKU-1048", "name": "Industrial Vacuum Cleaner", "stock_level": 15, "price": 540.20},
    {"sku": "SKU-1049", "name": "Heavy Duty Zip Ties (1000 Pack)", "stock_level": 500, "price": 25.99},
    {"sku": "SKU-1050", "name": "Adjustable Packing Table", "stock_level": 9, "price": 670.00},
]

with app.app_context():
    # Create tables if they do not exist
    db.create_all()
    
    # Check if we already have items to avoid duplicates on multiple runs
    if InventoryItem.query.count() == 0:
        print("Seeding database...")
        for item_data in new_items:
            item = InventoryItem(**item_data)
            db.session.add(item)
        db.session.commit()
        print("Successfully added items to the database!")
    else:
        print("Database already contains items. Skipping seed.")