import csv
import json
import hashlib
import types
import sys
import pprint

#script used to convert from buildingmap_csv.txt to print the json to update solr with.
#inefficient way to convert already obtained csv into json

file = 'buildingmap_csv.txt'
if sys.argv[1] != None:
    file = sys.argv[1]

#print "FILE: " + file
prevRow = None
with open(file, 'rb') as f:
    reader = csv.reader(f)
    items = []
    split = False
    for row in reader:

        if len(row) == 0:
            split = True
            continue

        # defaults

        item = {}
        if split:

            item["locationCode"] = row[-1]
            item["locationName"] = row[0]
            item["address"] = ' '.join(row[1 : -3])

            float(row[-2])
            float(row[-3])

            item["locationGeo"] = row[-3] + "," + row[-2]

        else:
            item["locationCode"] = None
            item["locationName"] = row[0]
            item["address"] = ' '.join(row[1 : -2])

            float(row[-1])
            float(row[-2])

            item["locationGeo"] = row[-2] + "," + row[-1]


        item["id"] = hashlib.sha1(item["locationName"] + item["address"] + item["locationGeo"]).hexdigest()
        items.append(item)

        prevRow = row

    print json.dumps(items)
