import sys
import os
import json
import geojson
import traceback

from czml3 import Document, Packet, CZML_VERSION
from czml3.properties import Polygon, PositionList, Material, SolidColorMaterial, Color
from czml3.enums import HeightReferences

_min_lat = 0
_min_lon = 0
_max_lat = 0
_max_lon = 0

#
# 範囲内か判定
#
def checkRange(coords, min_lat, min_lon, max_lat, max_lon):
    if max_lat == 0:
        return True
    for lon, lat in coords:
        if lat >= min_lat and lat <= max_lat and lon >= min_lon and lon <= max_lon:
            return True
    return False


def createCzmlDoc(geojson_data, kl):

    packets = [Packet(id="document", name="box", version=CZML_VERSION)]
    #各Featureを処理
    for i, feature in enumerate(geojson_data["features"]):
        geom = feature.get("geometry", {})
        if geom.get("type") != "Polygon":
            continue  # Polygon以外は無視

        # 座標取り出し
        coords = geom["coordinates"][0]
        if not checkRange(coords, _min_lat, _min_lon, _max_lat, _max_lon):
            continue   #範囲外は無視
        
        czml_positions = []
        for lon, lat in coords:
            czml_positions.extend([lon, lat, 0])  # 頂点ごとの高度は0

        # プロパティ取得
        props = feature.get("properties", {})
        czml_id = props.get("id", f"polygon-{i+1}")
        name = props.get("name", f"Polygon {i+1}")
        height = props.get("bldg_height", 0)
        elevation = props.get("elevation", 0)
        target_val = props.get(kl["target_prop"], 0)
        c = getPropValColor(kl["levels"], target_val)
        
        if c is not None:
            # CZMLポリゴンパケット
            polygon_packet = Packet(
                id=czml_id, name=name,
                polygon=Polygon(
                    positions=PositionList(cartographicDegrees=czml_positions),
                    extrudedHeight=height-elevation + 0.5,
                    extrudedHeightReference=HeightReferences.RELATIVE_TO_GROUND,
                    material=Material(
                        solidColor=SolidColorMaterial(
                           color=c
                        )
                    )
                )
            )
            packets.append(polygon_packet)
    # CZMLファイル書き出し
    czml_doc = Document(packets=packets)
    
    return czml_doc

#
# GeoJSONファイル読み込み
#
def getGeoJson(filePath):
    try:
        with open(filePath, "r", encoding="utf-8") as f:
            geojson_data = geojson.load(f)
        if geojson_data.get("type") != "FeatureCollection":
            return None
        return geojson_data
    except:
       return None

def getPropValColor(levels, val):
    for l in levels:
        if l["range"][0] is None and l["range"][1] is not None:
            if val <= l["range"][1]:
                return Color(rgba=l["color"])
        if l["range"][0] is not None and l["range"][1] is not None:
            if val>=l["range"][0] and val <= l["range"][1]:
                return Color(rgba=l["color"])
        if l["range"][0] is not None and l["range"][1] is None:
            if val >= l["range"][0]:
                return Color(rgba=l["color"])
    # return  Color(rgba=[255,255,255,150])
    return None


def readSettingJson():
    base_name = os.path.splitext(os.path.basename(__file__))[0]
    
    json_filename = f"{base_name}.json"
    
    json_path = os.path.join(os.path.dirname(__file__), json_filename)
    
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)

    return data

def getSettingKindLevels(setting_json, kind):
    kindlevels = setting_json["kindlevels"]
    
    for kl in kindlevels:
       if kl["kind"] == kind:
           return kl;
    return None
def main():
    
    global _min_lat, _min_lon, _max_lat, _max_lon
    args = sys.argv
    
    try:
        setting_json = readSettingJson()
        
        kind = args[1]
        _min_lon = float(args[2])
        _max_lon = float(args[3])
        _min_lat = float(args[4])
        _max_lat = float(args[5])
        geoJsonFilePath = args[6]
        
        kl = getSettingKindLevels(setting_json, kind)
        geojson_data = getGeoJson(geoJsonFilePath)
        czml_doc = createCzmlDoc(geojson_data, kl)
    
        #output_path = "output_extruded.czml"
        # with open(output_path, "w", encoding="utf-8") as f:
        #   f.write(czml_doc.to_json(indent=None))
        
        print(czml_doc.to_json(indent=None))
        return 0
    except Exception as e:
        traceback.print_exc()
        sys.exit(-1)

if __name__ == '__main__':
    main()

