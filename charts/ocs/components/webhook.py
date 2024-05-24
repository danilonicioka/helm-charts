#!/usr/local/bin/python
# -*- coding: utf-8 -*-
#Author: Elienai Soares

from discord_webhook import DiscordWebhook, DiscordEmbed
import sys
import json

# Server CTIC
# url_webhook = "https://discord.com/api/webhooks/1128397405591711785/9WWYV2a_Eoa-9ydoUN5NpBx_rTfPrfurJ4eTJ_qBXIba5RfqNMtcigOvRj76sGcT3ik_"

# Server Teste
url_webhook = "https://discord.com/api/webhooks/1113151706121912382/S_1W58-TSR6e8gaQV9MuqOmTfR9X8liahOiuYX_S06DutHJrYm9-up0PRaq-ZuUB9Z3B"

condition = sys.argv[1]
filename = ''

if (condition == 'add'):
  filename = "./notification_add.txt"
else:
  filename = "./notification_remove.txt"

with open(filename, "r") as file:
  json_str = file.read()

json_data = json.loads(json_str)

# Procura pelo nome do ativo modificado que aqui é chamado de "ASSET"
# Caso não encontre, procura recursivamente em todos os valores do dicionário
def get_asset_value(data):
    if "ASSET" in data:
        return data["ASSET"]
    else:
        for value in data.values():
            if isinstance(value, dict):
                result = get_asset_value(value)
                if result is not None:
                    return result
    return "Sem informação do nome do ativo"

# Entra em cada dicionario recursivamente extraindo chave e valor ignorando a chave "ASSET"
def process_json_data(data):
    result = []
    for key, value in data.items():
        if isinstance(value, dict):
            if "ASSET" in value:
                result.append(", ".join([f"{k}: '{v}'" for k, v in value.items() if k != 'ASSET']))
            else:
                result.extend(process_json_data(value))
    return result

result_cpus = ", ".join(process_json_data(json_data.get("cpus", {})))
result_memories = ", ".join(process_json_data(json_data.get("memories", {})))
result_monitors = ", ".join(process_json_data(json_data.get("monitors", {})))
result_storages = ", ".join(process_json_data(json_data.get("storages", {})))
result_videos = ", ".join(process_json_data(json_data.get("videos", {})))

bot_name = "OCS - Notification"
title = get_asset_value(json_data)
webhook = DiscordWebhook(url='%s' %(url_webhook))
embed = DiscordEmbed(title='Adição - %s' %(title), color='0x6aff00') if condition == 'add' else DiscordEmbed(title='Remoção - %s' %(title), color='0xCC0000')
embed.set_author(name='%s' %(bot_name))

embed.add_embed_field(name='Cpus', value=result_cpus, inline=False)
embed.add_embed_field(name='Memories', value=result_memories, inline=False)
embed.add_embed_field(name='Monitors', value=result_monitors, inline=False)
embed.add_embed_field(name='Storages', value=result_storages, inline=False)
embed.add_embed_field(name='Videos', value=result_videos, inline=False)

webhook.add_embed(embed)
response = webhook.execute(embed)