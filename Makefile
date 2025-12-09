#!make

# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#      ____                               _                                   #
#     / __ \____  ___  ____  ____ _____  (_) ®                                #
#    / / / / __ \/ _ \/ __ \/ __ `/ __ \/ /                                   #
#   / /_/ / /_/ /  __/ / / / /_/ / /_/ / /                                    #
#   \____/ .___/\___/_/ /_/\__,_/ .___/_/                                     #
#       /_/                    /_/                                            #
#                                                                             #
#   The Largest Certified API Marketplace                                     #
#   Accelerate Digital Transformation • Simplify Processes • Lead Industry    #
#                                                                             #
#   ═══════════════════════════════════════════════════════════════════════   #
#                                                                             #
#   Project:        openapi-rust-sdk                                          #
#   Version:        0.1.0                                                     #
#   Author:         Michael Cuffaro (@maiku1008)                              #
#   Copyright:      (c) 2025 Openapi®. All rights reserved.                   #
#   License:        MIT                                                       #
#   Maintainer:     Francesco Bianco                                          #
#   Contact:        https://openapi.com/                                      #
#   Repository:     https://github.com/openapi/openapi-php-sdk/               #
#   Documentation:  https://console.openapi.com/                              #
#                                                                             #
#   ═══════════════════════════════════════════════════════════════════════   #
#                                                                             #
#   "Truth lies at the source of the stream."                                 #
#                                  — English Proverb                          #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

## =========
## Variables
## =========

VERSION := 1.2.1

## ====================
## Development Commands
## ====================

dev-push:
	@git config credential.helper 'cache --timeout=3600'
	@git add .
	@git commit -m "$$(read -p 'Commit message: ' msg; echo $$msg)" || true
	@git push

## ================
## Release Commands
## ================

push:
	@git add .
	@git commit -am "Updated at $$(date)" || true
	@git push

release: push
	@git add .
	@git commit -m "Update PHP SDK to version ${VERSION}" || echo "No changes to commit"
	@git tag -fa "${VERSION}" -m "${VERSION}"
	@git push origin --tags -f
