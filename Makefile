# Joomla Component Makefile — com_ysinventory

COMPONENT_NAME := com_ysinventory
MANIFEST := ysinventory.xml
UPDATE_XML := com_ysinventory.update.xml
INSTALL_DIR ?= installation
GITHUB_OWNER ?= alexyarmoshko
GITHUB_REPO ?= joomla_com_ysinventory

VERSION := $(shell awk -F'[<>]' '/<version>/{print $$3; exit}' "$(MANIFEST)")
ZIP_VERSION := $(subst .,-,$(VERSION))
ZIP_NAME := $(COMPONENT_NAME)-v$(ZIP_VERSION).zip
ZIP_PATH := $(INSTALL_DIR)/$(ZIP_NAME)

PACKAGE_FILES := $(MANIFEST) LICENSE admin site media

.PHONY: info dist clean

info:
	@echo "Component:       $(COMPONENT_NAME)"
	@echo "Version:         $(VERSION)"
	@echo "Package files:   $(PACKAGE_FILES)"
	@echo "Package output:  $(ZIP_PATH)"
	@echo "Update feed:     $(UPDATE_XML)"

dist: clean $(ZIP_PATH) $(UPDATE_XML)
	@echo "--- Updating extension update XML ---"
	@SHA256="$$( (command -v sha256sum >/dev/null && sha256sum "$(ZIP_PATH)" || shasum -a 256 "$(ZIP_PATH)") | awk '{print $$1}' )"; \
	URL="https://github.com/$(GITHUB_OWNER)/$(GITHUB_REPO)/releases/download/$(VERSION)/$(ZIP_NAME)"; \
	echo "Package SHA256: $$SHA256"; \
	awk -v version="$(VERSION)" \
	    -v url="$$URL" \
	    -v sha="$$SHA256" '{ \
		if ($$0 ~ /<version>[^<]+<\/version>/) { \
			sub(/<version>[^<]+<\/version>/, "<version>" version "</version>"); \
		} else if ($$0 ~ /<downloadurl[^>]*>[^<]+<\/downloadurl>/) { \
			sub(/<downloadurl[^>]*>[^<]+<\/downloadurl>/, "<downloadurl type=\"full\" format=\"zip\">" url "</downloadurl>"); \
		} else if ($$0 ~ /<sha256>[^<]+<\/sha256>/) { \
			sub(/<sha256>[^<]+<\/sha256>/, "<sha256>" sha "</sha256>"); \
		} \
		print; \
	}' "$(UPDATE_XML)" > "$(UPDATE_XML).tmp" && mv "$(UPDATE_XML).tmp" "$(UPDATE_XML)"
	@echo ""
	@echo "=== Build complete ==="
	@echo "  $(ZIP_PATH)"
	@echo "  $(UPDATE_XML)"

$(ZIP_PATH): $(PACKAGE_FILES)
	@mkdir -p "$(INSTALL_DIR)"
	@cd "$(CURDIR)" && zip -r -X "$(ZIP_PATH)" $(PACKAGE_FILES) -x "*.DS_Store" -x "*/.DS_Store"
	@echo "Built $(ZIP_PATH)"

clean:
	@rm -f "$(ZIP_PATH)"
