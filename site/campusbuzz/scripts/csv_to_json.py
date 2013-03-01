import csv
import json
import hashlib
import types

#script used to convert from buildingmap_csv.txt to print the json to update solr with.
#inefficient way to convert already obtained csv into json

with open('buildingmap_csv.txt', 'rb') as f:
    reader = csv.reader(f)
    items = []
    for row in reader:
        if len(row) < 4:
            continue

        float(row[2])
        float(row[3])            

        item = {}
        item["locationName"] = row[0]
        item["address"] = row[1]
        item["locationGeo"] = row[2] + "," + row[3]
        if len(row) == 5:
            item["locationCode"] = row[4]
        else:
            item["locationCode"] = None

        item["id"] = hashlib.sha1(item["locationName"] + item["address"] + item["locationGeo"]).hexdigest()
        items.append(item)

    print json.dumps(items)
