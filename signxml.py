import argparse
from lxml import etree
import xmlsig
from datetime import datetime
from xades import XAdESContext, template, utils
from cryptography.hazmat.primitives.serialization import pkcs12
import base64

# Crear el analizador de argumentos
parser = argparse.ArgumentParser()
parser.add_argument('--xml', help='String base 64 XML')
parser.add_argument('--p12', help='File path .p12')
parser.add_argument('--password', help='Password')
args = parser.parse_args()

# Acceder a los argumentos
xml = base64.b64decode(args.xml)
p12_path = args.p12
password = args.password
def sing_xml(xml, p12_path, password):
    root = etree.fromstring(xml)
    signature = xmlsig.template.create(
        xmlsig.constants.TransformInclC14N,
        xmlsig.constants.TransformRsaSha1,
        "Signature",
    )
    ki = xmlsig.template.ensure_key_info(signature, name="KI")
    data = xmlsig.template.add_x509_data(ki)
    xmlsig.template.x509_data_add_certificate(data)
    xmlsig.template.add_key_value(ki)
    qualifying = template.create_qualifying_properties(signature)
    utils.ensure_id(qualifying)
    utils.ensure_id(qualifying)
    props = template.create_signed_properties(qualifying, datetime=datetime.now())
    idprops = utils.ensure_id(props)
    signed_do = template.ensure_signed_data_object_properties(props)
    xmlsig.template.add_reference(
        signature, xmlsig.constants.TransformSha1, uri="#" + idprops, name="RSP",
        uri_type='http://uri.etsi.org/01903#SignedProperties'
    )
    xmlsig.template.add_reference(
        signature, xmlsig.constants.TransformSha1, uri="#KI", name="RKI"
    )
    ref = xmlsig.template.add_reference(
        signature, xmlsig.constants.TransformSha1, uri="#comprobante", name="R1"
    )
    xmlsig.template.add_transform(ref, xmlsig.constants.TransformEnveloped)
    template.add_data_object_format(
        signed_do,
        "#R1",
        mime_type="text/xml",
        encoding="UTF-8",
    )
    root.append(signature)
    ctx = XAdESContext()
    with open(p12_path, "rb") as key_file:
        ctx.load_pkcs12(pkcs12.load_key_and_certificates(key_file.read(), bytes(password, 'utf-8')))
        ctx.sign(signature)
    return etree.tostring(root, encoding="utf-8", pretty_print=True)
try:
    print(sing_xml(xml, p12_path, password).decode('utf-8'))
except Exception as e:
    print(None)
    print(e)


